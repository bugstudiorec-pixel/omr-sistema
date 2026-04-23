# Sistema OMR – Deploy no Railway

## Como colocar online (passo a passo)

### 1. Crie uma conta no GitHub
Acesse https://github.com e crie uma conta gratuita.

### 2. Crie um repositório no GitHub
- Clique em "New repository"
- Nome: `omr-sistema` (ou qualquer nome)
- Deixe como **Private** (privado)
- Clique em "Create repository"

### 3. Faça upload dos arquivos
Na página do repositório criado:
- Clique em "uploading an existing file"
- Arraste TODOS os arquivos desta pasta para lá
- Clique em "Commit changes"

### 4. Crie uma conta no Railway
Acesse https://railway.app e clique em **"Start a New Project"**
- Faça login com sua conta do GitHub

### 5. Deploy no Railway
- Clique em **"Deploy from GitHub repo"**
- Selecione o repositório `omr-sistema`
- Railway vai detectar o Dockerfile automaticamente
- Aguarde o build (5–10 minutos na primeira vez)

### 6. Gere o domínio público
- No painel do Railway, clique no seu projeto
- Vá em **Settings → Networking → Generate Domain**
- Seu site estará disponível em: `https://seu-projeto.up.railway.app`

---

## Observações importantes

- O banco SQLite é criado automaticamente na primeira execução
- Os uploads ficam salvos no servidor (use com moderação no plano gratuito)
- O plano gratuito do Railway oferece **500 horas/mês**
- Para uso contínuo, considere o plano pago (~$5/mês)
