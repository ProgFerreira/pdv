<?php

/**
 * Funções de cálculo para Ficha Técnica / Formação de Preço.
 * Unidades: kg, g, l, ml, un
 */

declare(strict_types=1);

/**
 * Calcula rendimento % a partir de quantidade líquida e bruta.
 * yield% = (qty_net / qty_brut) * 100
 */
function calc_yield_percent(float $qtyNet, float $qtyBrut): ?float
{
    if ($qtyBrut <= 0) {
        return null;
    }
    return round(($qtyNet / $qtyBrut) * 100, 2);
}

/**
 * Converte quantidade para a unidade do ingrediente e calcula custo do item.
 * ingredient_unit: kg, g, l, ml, un
 * item_unit: g, kg, ml, l, un
 * cost_per_unit = custo por unidade do ingrediente (ex: R$/kg, R$/un)
 */
function calc_item_total_cost(
    float $qtyBrut,
    string $itemUnit,
    string $ingredientUnit,
    float $costPerUnit
): float {
    $itemUnit = strtolower($itemUnit);
    $ingredientUnit = strtolower($ingredientUnit);
    if ($qtyBrut <= 0 || $costPerUnit < 0) {
        return 0.0;
    }

    // Converter qty_brut para a unidade do ingrediente
    $qtyInIngredientUnit = convert_to_ingredient_unit($qtyBrut, $itemUnit, $ingredientUnit);
    return round($qtyInIngredientUnit * $costPerUnit, 4);
}

/**
 * Converte quantidade da unidade do item para a unidade do ingrediente.
 */
function convert_to_ingredient_unit(float $qty, string $fromUnit, string $toUnit): float
{
    $fromUnit = strtolower($fromUnit);
    $toUnit = strtolower($toUnit);
    if ($fromUnit === $toUnit) {
        return $qty;
    }
    // Normalizar para unidade base: kg, l, un
    $toBase = to_base_unit($qty, $fromUnit);
    $fromBase = $toBase['value'];
    $baseType = $toBase['type']; // 'mass', 'volume', 'un'

    $toBaseTarget = to_base_unit(1, $toUnit);
    $targetType = $toBaseTarget['type'];

    if ($baseType !== $targetType) {
        // Incompatível (ex: g vs l) – não converter, retornar 0 para evitar custo errado
        return 0.0;
    }

    // Converter da unidade base para a unidade do ingrediente
    if ($toUnit === 'kg') {
        return $fromBase; // já em kg
    }
    if ($toUnit === 'g') {
        return $fromBase * 1000;
    }
    if ($toUnit === 'l') {
        return $fromBase;
    }
    if ($toUnit === 'ml') {
        return $fromBase * 1000;
    }
    if ($toUnit === 'un') {
        return $fromBase;
    }
    return $fromBase;
}

/**
 * Converte quantidade para unidade base (kg para massa, l para volume, un para unidade).
 * @return array{value: float, type: string}
 */
function to_base_unit(float $qty, string $unit): array
{
    $unit = strtolower($unit);
    switch ($unit) {
        case 'kg':
            return ['value' => $qty, 'type' => 'mass'];
        case 'g':
            return ['value' => $qty / 1000, 'type' => 'mass'];
        case 'l':
            return ['value' => $qty, 'type' => 'volume'];
        case 'ml':
            return ['value' => $qty / 1000, 'type' => 'volume'];
        case 'un':
            return ['value' => $qty, 'type' => 'un'];
        default:
            return ['value' => $qty, 'type' => 'un'];
    }
}

/**
 * Dado item em g e ingrediente em kg: custo = (qty_g/1000) * custo_por_kg.
 * Dado item em kg e ingrediente em kg: custo = qty_kg * custo_por_kg.
 * Implementação direta das regras do enunciado (evita conversão genérica errada).
 */
function calc_item_cost_simple(
    float $qtyBrut,
    string $itemUnit,
    string $ingredientUnit,
    float $costPerUnit
): float {
    $iu = strtolower($itemUnit);
    $gu = strtolower($ingredientUnit);
    if ($qtyBrut <= 0 || $costPerUnit < 0) {
        return 0.0;
    }
    // Mesma unidade
    if ($iu === $gu) {
        return round($qtyBrut * $costPerUnit, 4);
    }
    // Item em g, ingrediente em kg
    if ($iu === 'g' && $gu === 'kg') {
        return round(($qtyBrut / 1000) * $costPerUnit, 4);
    }
    // Item em kg, ingrediente em g
    if ($iu === 'kg' && $gu === 'g') {
        return round($qtyBrut * 1000 * $costPerUnit, 4);
    }
    // Item em ml, ingrediente em l
    if ($iu === 'ml' && $gu === 'l') {
        return round(($qtyBrut / 1000) * $costPerUnit, 4);
    }
    // Item em l, ingrediente em ml
    if ($iu === 'l' && $gu === 'ml') {
        return round($qtyBrut * 1000 * $costPerUnit, 4);
    }
    // un com un
    if ($iu === 'un' && $gu === 'un') {
        return round($qtyBrut * $costPerUnit, 4);
    }
    // Outras combinações: usar conversão genérica
    $qtyInIngredient = convert_qty_to_ingredient_unit($qtyBrut, $iu, $gu);
    return round($qtyInIngredient * $costPerUnit, 4);
}

/**
 * Converte qty na unidade do item para a mesma unidade do ingrediente (valor direto para multiplicar pelo custo).
 */
function convert_qty_to_ingredient_unit(float $qty, string $itemUnit, string $ingredientUnit): float
{
    $iu = strtolower($itemUnit);
    $gu = strtolower($ingredientUnit);
    if ($iu === $gu) {
        return $qty;
    }
    // massa
    if (in_array($iu, ['g', 'kg'], true) && in_array($gu, ['g', 'kg'], true)) {
        if ($iu === 'g' && $gu === 'kg') {
            return $qty / 1000;
        }
        if ($iu === 'kg' && $gu === 'g') {
            return $qty * 1000;
        }
    }
    // volume
    if (in_array($iu, ['ml', 'l'], true) && in_array($gu, ['ml', 'l'], true)) {
        if ($iu === 'ml' && $gu === 'l') {
            return $qty / 1000;
        }
        if ($iu === 'l' && $gu === 'ml') {
            return $qty * 1000;
        }
    }
    return 0.0;
}

/**
 * Preço sugerido por margem bruta: price = cost_total / (1 - margin_percent/100)
 */
function calc_suggested_price(float $costTotal, float $marginPercent): float
{
    if ($marginPercent >= 100 || $marginPercent < 0) {
        return 0.0;
    }
    $factor = 1 - ($marginPercent / 100);
    if ($factor <= 0) {
        return 0.0;
    }
    return round($costTotal / $factor, 2);
}

/**
 * Unidades permitidas para select (item e ingrediente).
 * @return array<string, string>
 */
function technical_sheet_unit_options(): array
{
    return [
        'g'  => 'g',
        'kg' => 'kg',
        'ml' => 'ml',
        'l'  => 'l',
        'un' => 'un',
    ];
}
