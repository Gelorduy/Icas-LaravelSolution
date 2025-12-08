#!/bin/bash
# Generate self-signed SSL certificate for local development with multiple domain support

CERT_DIR="./certs"
CERT_FILE="$CERT_DIR/icas.crt"
KEY_FILE="$CERT_DIR/icas.key"
CSR_FILE="$CERT_DIR/icas.csr"
CONFIG_FILE="$CERT_DIR/icas.conf"

# Create certs directory if it doesn't exist
mkdir -p "$CERT_DIR"

# Check if certificate already exists
if [ -f "$CERT_FILE" ] && [ -f "$KEY_FILE" ]; then
    echo "✓ SSL certificates already exist at $CERT_DIR"
    exit 0
fi

echo "Generating self-signed SSL certificate for local development..."
echo "Domains: localhost, icas.local, www.icas.local"
echo ""

# Create OpenSSL config for Subject Alternative Names (SAN)
cat > "$CONFIG_FILE" << 'EOF'
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = req_distinguished_name
req_extensions = v3_req

[req_distinguished_name]
C = US
ST = Local
L = Development
O = ICAS
CN = icas.local

[v3_req]
subjectAltName = @alt_names

[alt_names]
DNS.1 = localhost
DNS.2 = icas.local
DNS.3 = www.icas.local
DNS.4 = 127.0.0.1
EOF

# Generate private key and certificate with SAN
openssl req -x509 -newkey rsa:2048 \
    -keyout "$KEY_FILE" \
    -out "$CERT_FILE" \
    -days 365 \
    -nodes \
    -config "$CONFIG_FILE"

if [ $? -eq 0 ]; then
    echo "✓ Self-signed certificate generated successfully!"
    echo ""
    echo "Certificate details:"
    echo "  - Certificate: $CERT_FILE"
    echo "  - Private Key: $KEY_FILE"
    echo "  - Valid for: 365 days"
    echo "  - Subject Alternative Names (SANs):"
    echo "    • localhost"
    echo "    • icas.local"
    echo "    • www.icas.local"
    echo "    • 127.0.0.1"
    echo ""
    echo "Browser Setup:"
    echo "  1. Add to your /etc/hosts file:"
    echo "     127.0.0.1 localhost icas.local www.icas.local"
    echo ""
    echo "  2. Access via:"
    echo "     https://icas.local"
    echo "     https://www.icas.local"
    echo "     https://localhost"
    echo ""
    echo "  3. Accept the self-signed certificate warning (browser-specific)"
    echo ""
    echo "Development environment is ready!"
    
    # Clean up config file
    rm -f "$CONFIG_FILE"
else
    echo "✗ Failed to generate certificate. Make sure openssl is installed."
    rm -f "$CONFIG_FILE"
    exit 1
fi
