#!/bin/bash

# 🧪 Test CSRF Flow - Vérifier que le login fonctionne

echo "🔍 Test 1 : Initialiser le CSRF token..."
CSRF_RESPONSE=$(curl -s -c cookies.txt \
  -X GET http://localhost:8000/api/v1/csrf-cookie \
  -H "Accept: application/json")

echo "Response: $CSRF_RESPONSE"
echo ""

echo "🔍 Cookies reçus :"
cat cookies.txt
echo ""

echo "🔍 Test 2 : Extraire le token XSRF..."
XSRF_TOKEN=$(grep XSRF-TOKEN cookies.txt | awk '{print $7}')
echo "XSRF-TOKEN: $XSRF_TOKEN"
echo ""

echo "🔍 Test 3 : Tenter un login..."
curl -s -b cookies.txt \
  -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  -d '{"email":"admin@example.com","password":"password"}' | jq .

echo ""
echo "✅ Test terminé ! Cherchez les codes 200 pour succès."
