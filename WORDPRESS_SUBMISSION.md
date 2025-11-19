# WordPress.org Plugin Submission Guide

## Preparação

### 1. Criar Conta no WordPress.org
- Acesse: https://wordpress.org/support/register.php
- Registre-se com seu email
- Confirme o email

### 2. Arquivos Necessários ✅

Já incluídos no projeto:
- ✅ `n8nwoo.php` - Plugin principal
- ✅ `readme.txt` - Descrição para WordPress.org
- ✅ `LICENSE` - Licença MIT
- ✅ `assets/banner-1544x500.svg` - Banner grande
- ✅ `assets/banner-772x250.svg` - Banner pequeno
- ✅ `assets/icon-256x256.svg` - Ícone do plugin

### 3. Validação do readme.txt

Valide em: https://wordpress.org/plugins/developers/readme-validator/

Cole o conteúdo de `readme.txt` e corrija qualquer erro.

## Submissão

### Passo 1: Submetendo o Plugin

1. Acesse: https://wordpress.org/plugins/developers/add/
2. Faça login com sua conta WordPress.org
3. Cole a URL do repositório GitHub: `https://github.com/williamschonsdev/n8n-woocommerce`
4. Ou faça upload do arquivo ZIP do plugin

### Passo 2: Aguardar Aprovação

- **Tempo**: Geralmente 2-10 dias úteis
- **Notificação**: Você receberá email quando for aprovado
- **Revisão**: Equipe WordPress.org revisa segurança e qualidade do código

### Passo 3: Após Aprovação

Você receberá um repositório SVN no formato:
```
https://plugins.svn.wordpress.org/n8n-woocommerce-webhook/
```

## Publicando no SVN

### 1. Instalar SVN (se necessário)
```bash
# macOS
brew install subversion

# Windows
# Baixe TortoiseSVN: https://tortoisesvn.net/
```

### 2. Checkout do Repositório
```bash
svn co https://plugins.svn.wordpress.org/seu-plugin-slug/
cd seu-plugin-slug
```

### 3. Estrutura de Pastas
```
trunk/              # Versão de desenvolvimento
  n8nwoo.php
  readme.txt
  LICENSE
  ...
tags/               # Versões estáveis
  1.0.1/
assets/             # Imagens (fora do trunk)
  banner-1544x500.svg
  banner-772x250.svg
  icon-256x256.svg
  screenshot-1.png (opcional)
```

### 4. Adicionar Arquivos

```bash
# Copiar arquivos para trunk
cp -r /caminho/do/projeto/* trunk/

# Copiar assets
cp -r /caminho/do/projeto/assets/* assets/

# Adicionar ao SVN
svn add trunk/*
svn add assets/*

# Commit
svn ci -m "Initial release v1.0.1"
```

### 5. Criar Tag de Versão

```bash
# Copiar trunk para tag
svn cp trunk tags/1.0.1

# Commit da tag
svn ci -m "Tagging version 1.0.1"
```

## Após Publicação

### Atualizar Plugin

1. Edite os arquivos em `trunk/`
2. Atualize o número da versão em:
   - `n8nwoo.php` (header)
   - `readme.txt` (Stable tag)
3. Commit as mudanças
4. Crie nova tag com a versão

```bash
# Editar e commit no trunk
svn ci -m "Update to version 1.0.2"

# Criar nova tag
svn cp trunk tags/1.0.2
svn ci -m "Tagging version 1.0.2"
```

## Dicas Importantes

### Qualidade do Código
- ✅ Use WordPress coding standards
- ✅ Escape all output
- ✅ Sanitize all input
- ✅ Use nonces for forms
- ✅ No eval() ou códigos perigosos

### readme.txt
- ✅ Preencha todas as seções
- ✅ Use formatação correta
- ✅ Adicione screenshots (opcional mas recomendado)
- ✅ Mantenha changelog atualizado

### Assets
- ✅ Banner: 1544x500 e 772x250
- ✅ Ícone: 256x256
- ✅ Formato: PNG ou SVG
- ✅ Screenshots: 772x250 ou maior

### SVG para PNG (se necessário)

WordPress.org aceita SVG, mas se preferir PNG:

```bash
# Usando ImageMagick ou converter online
# https://cloudconvert.com/svg-to-png
```

## Checklist Final

Antes de submeter:

- [ ] Plugin testado em WordPress atual
- [ ] Plugin testado em WooCommerce atual
- [ ] Sem erros PHP
- [ ] Sem avisos de segurança
- [ ] readme.txt validado
- [ ] Versão correta em todos arquivos
- [ ] Assets criados e otimizados
- [ ] Licença clara (MIT)
- [ ] Código limpo e comentado
- [ ] Funcionalidades testadas

## Recursos Úteis

- **Handbook**: https://developer.wordpress.org/plugins/
- **Guidelines**: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- **SVN Guide**: https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
- **Readme Validator**: https://wordpress.org/plugins/developers/readme-validator/

## Suporte

Após a publicação:
- Responda dúvidas no fórum WordPress.org
- Mantenha o plugin atualizado
- Corrija bugs reportados
- Adicione novas funcionalidades

Boa sorte com a submissão! 🚀
