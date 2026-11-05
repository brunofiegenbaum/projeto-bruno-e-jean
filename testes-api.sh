#!/bin/bash
# Testes simples da API de veiculos.
# Antes de rodar: configure API_TOKEN e inicie Apache e MySQL no XAMPP.

URL="${API_URL:-http://localhost/projetosemestre/api/veiculos.php}"
USUARIO_ID="${USUARIO_ID:-1}"
TOKEN="${API_TOKEN:-}"

if [ -z "$TOKEN" ]; then
    echo "Erro: defina API_TOKEN antes de executar."
    exit 1
fi

RESPOSTA=$(mktemp)
ID=""
PASSOU=0
FALHOU=0

limpar() {
    if [ -n "$ID" ]; then
        curl -s -o /dev/null -X DELETE -H "Authorization: Bearer $TOKEN" "$URL?id=$ID"
    fi
    rm -f "$RESPOSTA"
}
trap limpar EXIT

testar() {
    NOME="$1"
    ESPERADO="$2"
    shift 2
    RECEBIDO=$(curl -sS -o "$RESPOSTA" -w "%{http_code}" "$@")
    if [ "$RECEBIDO" = "$ESPERADO" ]; then
        echo "OK: $NOME (HTTP $RECEBIDO)"
        PASSOU=$((PASSOU + 1))
    else
        echo "ERRO: $NOME (esperado $ESPERADO, recebido $RECEBIDO)"
        cat "$RESPOSTA"
        echo
        FALHOU=$((FALHOU + 1))
    fi
}

echo "Testando API: $URL"

testar "GET sem token" 401 "$URL"
testar "GET com token errado" 401 -H "Authorization: Bearer token-errado" "$URL"
testar "GET listar" 200 -H "Authorization: Bearer $TOKEN" "$URL"
testar "GET ID invalido" 400 -H "Authorization: Bearer $TOKEN" "$URL?id=abc"
testar "GET inexistente" 404 -H "Authorization: Bearer $TOKEN" "$URL?id=999999999"
testar "Metodo nao permitido" 405 -X PATCH -H "Authorization: Bearer $TOKEN" "$URL"
testar "POST sem campos" 400 -X POST -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -d '{}' "$URL"

DADOS="{\"id_usuario\":$USUARIO_ID,\"marca\":\"Fiat\",\"modelo\":\"Uno Teste\",\"ano\":2015,\"preco\":20000,\"quilometragem\":50000}"
testar "POST cadastrar" 201 -X POST -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -d "$DADOS" "$URL"
ID=$(sed -n 's/.*"id_veiculo":\([0-9][0-9]*\).*/\1/p' "$RESPOSTA")

if [ -n "$ID" ]; then
    testar "GET por ID" 200 -H "Authorization: Bearer $TOKEN" "$URL?id=$ID"
    NOVOS_DADOS="{\"id_usuario\":$USUARIO_ID,\"marca\":\"Fiat\",\"modelo\":\"Uno Atualizado\",\"ano\":2016,\"preco\":21000,\"quilometragem\":45000}"
    testar "PUT atualizar" 200 -X PUT -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -d "$NOVOS_DADOS" "$URL?id=$ID"
    testar "DELETE excluir" 200 -X DELETE -H "Authorization: Bearer $TOKEN" "$URL?id=$ID"
    ID=""
else
    echo "ERRO: POST nao retornou id_veiculo. Testes de GET por ID, PUT e DELETE ignorados."
    FALHOU=$((FALHOU + 3))
fi

echo
echo "Resultado: $PASSOU passaram, $FALHOU falharam."
if [ "$FALHOU" -gt 0 ]; then
    exit 1
fi
