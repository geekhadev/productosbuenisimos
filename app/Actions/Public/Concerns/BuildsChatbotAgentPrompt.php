<?php

namespace App\Actions\Public\Concerns;

trait BuildsChatbotAgentPrompt
{
    /**
     * @param  array{id: string, name: string, code: string, sku: string, price: float|int|string}|null  $productContext
     */
    protected function buildAgentPrompt(string $message, string $phone, ?array $productContext = null): string
    {
        $blocks = [$this->buildVisitorPhoneContext($phone)];

        if ($productContext !== null) {
            $blocks[] = $this->buildProductContextBlock($productContext);
        }

        $blocks[] = "Mensaje del visitante: {$message}";

        return implode("\n\n", $blocks);
    }

    protected function buildVisitorPhoneContext(string $phone): string
    {
        return <<<PROMPT
[Contexto del visitante — chatbot público]
- El visitante ya ingresó su teléfono al iniciar el chat: {$phone}
- Estás conversando directamente con esa persona (cliente final), no con un operador interno.
- NO vuelvas a pedir el número de teléfono.
- Si aún no identificaste al cliente en esta conversación, invoca de inmediato `get_customer_by_phone` con ese número y continúa el flujo desde ahí.
PROMPT;
    }

    /**
     * @param  array{id: string, name: string, code: string, sku: string, price: float|int|string}  $productContext
     */
    protected function buildProductContextBlock(array $productContext): string
    {
        $price = is_numeric($productContext['price'])
            ? number_format((float) $productContext['price'], 0, ',', '.')
            : (string) $productContext['price'];

        return <<<PROMPT
[Contexto del producto consultado]
- Nombre: {$productContext['name']}
- Código: {$productContext['code']}
- SKU: {$productContext['sku']}
- Precio: \${$price}
PROMPT;
    }
}
