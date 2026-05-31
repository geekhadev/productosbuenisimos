<?php

use App\Enums\ChatbotMessageRole;
use App\Enums\ChatbotSource;
use App\Enums\Stock\ProductMediaType;
use App\Models\Company;
use App\Models\Public\ChatbotConversation;
use App\Models\Public\ChatbotMessage;
use App\Models\Stock\Product;
use App\Models\Stock\ProductMedia;
use App\Support\Chatbot\ResolveChatbotVideoAttachments;
use App\Support\ChatbotCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\ToolResult;
use Laravel\Ai\Responses\Data\Usage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->resolver = new ResolveChatbotVideoAttachments;
});

test('sanitizeAgentText removes placeholder video markdown links', function () {
    $text = "Aquí tiene el video: [Ver Video](#)\n\n¿Desea continuar?";

    expect($this->resolver->sanitizeAgentText($text))
        ->toBe("Aquí tiene el video:\n\n¿Desea continuar?");
});

test('resolve attaches product video when agent presents matched product', function () {
    $company = Company::factory()->create(['name' => ChatbotCompany::NAME]);
    $product = Product::factory()->for($company)->create([
        'name' => 'Imanes de Neodimio Autoadhesivos',
        'is_active' => true,
    ]);

    ProductMedia::factory()->create([
        'product_id' => $product->id,
        'type' => ProductMediaType::Video,
        'path' => 'products/'.$product->id.'/videos/demo.mp4',
        'mime_type' => 'video/mp4',
        'original_name' => 'demo.mp4',
    ]);

    $conversation = ChatbotConversation::factory()->for($company)->create();
    ChatbotMessage::factory()->for($conversation, 'conversation')->create([
        'role' => ChatbotMessageRole::User,
        'source' => ChatbotSource::Web,
        'content' => 'quiero 2 imanes',
    ]);

    $response = new AgentResponse(
        'invocation-id',
        'Con gusto, Don Irwing. Le comparto el video de funcionamiento de los imanes.',
        new Usage(10, 20),
        new Meta,
    );

    $attachments = $this->resolver->resolve(
        companyId: $company->id,
        response: $response,
        userMessage: 'irwing',
        productContext: null,
        conversation: $conversation,
    );

    expect($attachments)->toHaveCount(1)
        ->and($attachments[0]['type'])->toBe('video')
        ->and($attachments[0]['url'])->toBe('/storage/products/'.$product->id.'/videos/demo.mp4')
        ->and($attachments[0]['product_name'])->toBe($product->name);
});

test('resolve attaches video from get_products tool result', function () {
    $company = Company::factory()->create(['name' => ChatbotCompany::NAME]);
    $conversation = ChatbotConversation::factory()->for($company)->create();

    $response = new AgentResponse(
        'invocation-id',
        'Aquí tiene la información del producto.',
        new Usage(10, 20),
        new Meta,
    );

    $response->withToolCallsAndResults(
        toolCalls: collect(),
        toolResults: collect([
            new ToolResult(
                id: 'tool-1',
                name: 'get_products',
                arguments: [],
                result: json_encode([
                    [
                        'id' => 'product-1',
                        'name' => 'Imanes Premium',
                        'video' => ['url' => '/storage/products/demo.mp4'],
                    ],
                ], JSON_THROW_ON_ERROR),
            ),
        ]),
    );

    $attachments = $this->resolver->resolve(
        companyId: $company->id,
        response: $response,
        userMessage: 'quiero imanes',
        productContext: null,
        conversation: $conversation,
    );

    expect($attachments)->toHaveCount(1)
        ->and($attachments[0]['url'])->toBe('/storage/products/demo.mp4');
});

test('resolve skips video when agent only asks for customer name', function () {
    $company = Company::factory()->create(['name' => ChatbotCompany::NAME]);
    $product = Product::factory()->for($company)->create([
        'name' => 'Imanes de Neodimio Autoadhesivos',
        'is_active' => true,
    ]);

    ProductMedia::factory()->create([
        'product_id' => $product->id,
        'type' => ProductMediaType::Video,
        'path' => 'products/'.$product->id.'/videos/demo.mp4',
        'mime_type' => 'video/mp4',
        'original_name' => 'demo.mp4',
    ]);

    $conversation = ChatbotConversation::factory()->for($company)->create();

    $response = new AgentResponse(
        'invocation-id',
        '¿Podría indicarme su nombre completo, por favor?',
        new Usage(10, 20),
        new Meta,
    );

    $attachments = $this->resolver->resolve(
        companyId: $company->id,
        response: $response,
        userMessage: 'quiero 2 imanes',
        productContext: null,
        conversation: $conversation,
    );

    expect($attachments)->toBe([]);
});
