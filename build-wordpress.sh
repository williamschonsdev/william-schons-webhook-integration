#!/bin/bash

# Script para preparar o plugin para submissão no WordPress.org

echo "🚀 Preparando N8N WooCommerce Webhook para WordPress.org"
echo ""

# Criar diretório de build
echo "📦 Criando diretório de build..."
mkdir -p build/n8nwoo

# Copiar arquivos essenciais
echo "📋 Copiando arquivos do plugin..."
cp n8nwoo.php build/n8nwoo/
cp readme.txt build/n8nwoo/
cp LICENSE build/n8nwoo/

# Copiar assets
echo "🎨 Copiando assets..."
mkdir -p build/assets
cp assets/banner-*.svg build/assets/ 2>/dev/null || true
cp assets/icon-*.svg build/assets/ 2>/dev/null || true
cp assets/screenshots/*.png build/assets/ 2>/dev/null || true

# Criar arquivo ZIP
echo "📦 Criando arquivo ZIP..."
cd build
zip -r ../n8nwoo-1.0.1.zip n8nwoo/
cd ..

echo ""
echo "✅ Plugin preparado com sucesso!"
echo ""
echo "📂 Arquivos criados:"
echo "   - build/n8nwoo/ (pasta do plugin)"
echo "   - build/assets/ (imagens para WordPress.org)"
echo "   - n8nwoo-1.0.1.zip (arquivo para upload)"
echo ""
echo "📝 Próximos passos:"
echo "   1. Validar readme.txt em: https://wordpress.org/plugins/developers/readme-validator/"
echo "   2. Testar o plugin em ambiente WordPress limpo"
echo "   3. Submeter em: https://wordpress.org/plugins/developers/add/"
echo "   4. Aguardar aprovação (2-10 dias)"
echo ""
echo "📚 Documentação completa em: WORDPRESS_SUBMISSION.md"
echo ""
