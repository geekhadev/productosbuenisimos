<?php

namespace App\Actions\Public\Concerns;

trait BuildsChatbotAgentPrompt
{
    /**
     * @param  array{id: string, name: string, code: string, sku: string, price: float|int|string}|null  $productContext
     * @param  list<string>  $alreadySentVideoProductNames
     */
    protected function buildAgentPrompt(string $message, string $phone, ?array $productContext = null, array $alreadySentVideoProductNames = []): string
    {
        $blocks = [
            $this->buildVisitorPhoneContext($phone),
            $this->buildMediaContext($alreadySentVideoProductNames),
        ];

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
     * @param  list<string>  $alreadySentVideoProductNames
     */
    protected function buildMediaContext(array $alreadySentVideoProductNames = []): string
    {
        $base = <<<'PROMPT'
[Instrucciones de media — chatbot web]
- Antes de describir un producto, invoca `get_products` para obtener datos reales del catálogo.
- NO inventes enlaces de video ni uses markdown como [Ver Video](#). El sistema adjunta el video automáticamente cuando presentas un producto.
- En tu mensaje puedes mencionar que compartes el video de funcionamiento; no pegues URLs manualmente.
- Si el visitante ya mencionó un producto en mensajes anteriores, no vuelvas a preguntarle qué producto le interesa; continúa con la presentación de ese producto.
- Nunca invoques `get_similar_products` ni ofrezcas venta cruzada hasta haber registrado el pedido con `create_order`.
PROMPT;

        if ($alreadySentVideoProductNames !== []) {
            $list = implode(', ', $alreadySentVideoProductNames);
            $base .= "\n- Ya enviaste el video de los siguientes productos en esta conversación (NO vuelvas a mencionarlo ni a presentarlos como si fuera la primera vez): {$list}.";
        }

        return $base;
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
