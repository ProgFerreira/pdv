-- Garantir categorias para as abas do PDV (Bebidas e Sobremesas).
-- Se já existirem categorias com esses nomes (ou variações), não insere duplicata.
-- Execute uma vez; depois, as abas "Bebidas" e "Sobremesas" no PDV passam a filtrar os produtos por categoria.

INSERT INTO categories (name)
SELECT 'Bebidas' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories c WHERE LOWER(TRIM(c.name)) IN ('bebidas', 'bebida'));

INSERT INTO categories (name)
SELECT 'Sobremesas' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories c WHERE LOWER(TRIM(c.name)) IN ('sobremesas', 'sobremesa'));
