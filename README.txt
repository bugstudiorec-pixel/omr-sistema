SISTEMA OMR PHP + PYTHON/OPENCV

O que este pacote entrega
- PHP para interface, cadastro, upload, resultado e histórico
- Python + OpenCV para leitura OMR
- QR por token da aplicação
- Correção automática e correção manual por questão
- Histórico com filtro e exportação CSV
- Banco SQLite para teste local

Dependências
1) PHP com:
   - PDO SQLite
   - exec() habilitado
2) Python 3 com:
   - pip install opencv-python numpy pillow qrcode

Estrutura
- /public ............ páginas web
- /python ............ motor OMR e geração de QR
- /uploads ........... scans e QR gerados
- /temp .............. imagens alinhadas/debug
- /data/app.sqlite ... banco local
- /schema.sql ........ criação das tabelas
- /reset_bd.php ...... recria o banco do zero

Como rodar no UwAmp/XAMPP
1. Extraia a pasta em D:\UwAmp\www\sistema_omr_php_python
2. Instale as libs Python:
   pip install opencv-python numpy pillow qrcode
3. Acesse:
   http://localhost/sistema_omr_php_python/reset_bd.php
4. Depois abra:
   http://localhost/sistema_omr_php_python/public/

Fluxo
1. Cadastre o aluno
2. Cadastre a prova e o gabarito da versão A
3. Crie a aplicação e gere o QR do token
4. Imprima a folha contendo o QR e as marcas de sincronismo
5. Envie a foto/scan em Corrigir OMR
6. Revise manualmente, se necessário

Observações importantes
- O layout 14x4 já está calibrado para o template base enviado anteriormente
- Para outras folhas, crie/ajuste o JSON em /python/layouts/
- Para versões B/C da mesma prova, insira gabaritos extras na tabela gabaritos
- O QR guarda TOKEN e VERSAO; o PHP usa o TOKEN para localizar a aplicação
- O motor atual já faz warp de perspectiva pela maior folha encontrada, mas não substitui uma calibração fina para cada novo modelo impresso

Arquivos principais
- public/processar_omr.php ...... chama o Python via exec()
- python/omr_reader.py .......... lê QR, tenta alinhar e detectar bolhas
- public/resultado.php .......... mostra leitura e revisão manual
- public/historico.php .......... lista as correções
