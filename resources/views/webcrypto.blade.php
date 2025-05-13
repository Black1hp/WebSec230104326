@extends('layouts.master')
@section('title', 'WebCrypto')
@section('content')
    <div class="card m-4">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col">
                    <label for="inputText" class="form-label">Plain Text</label>
                    <textarea id="inputText" class="form-control" placeholder="Data" required></textarea>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <button type="button" id="generateKeyBtn" class="btn btn-info">Generate Key</button>
                    <button type="button" id="encryptBtn" class="btn btn-primary">Encrypt</button>
                    <button type="button" id="decryptBtn" class="btn btn-warning">Decrypt</button>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <label for="outputText" class="form-label">Cipher Text</label>
                    <textarea id="outputText" class="form-control" placeholder="Data" readonly></textarea>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <div id="keyInfo" class="alert alert-info">No key generated</div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    // Global variables for key and IV
    let key = null;
    let iv = null;

    // Generate Key and IV Button
    document.getElementById('generateKeyBtn').addEventListener('click', async function() {
        // Generate IV
        iv = window.crypto.getRandomValues(new Uint8Array(16));
        
        // Generate AES-CBC 256-bit key
        try {
            key = await window.crypto.subtle.generateKey({
                name: 'AES-CBC',
                length: 256
            }, true, ['encrypt', 'decrypt']);
            
            // Update UI
            document.getElementById('keyInfo').innerHTML = `
                Key Generated Successfully!
                <br>IV Length: ${iv.length} bytes
                <br>Key Usages: Encrypt, Decrypt
            `;
        } catch (error) {
            alert('Key Generation Error: ' + error);
        }
    });

    // Encryption Function
    async function encryptCBC() {
        const inputText = document.getElementById('inputText');
        const outputText = document.getElementById('outputText');
        
        if (!key || !iv) {
            alert('Please generate a key first!');
            return;
        }
        
        try {
            const encodedText = new TextEncoder().encode(inputText.value);
            const encryptedData = await window.crypto.subtle.encrypt({
                name: 'AES-CBC',
                iv: iv,
            }, key, encodedText);
            
            const encryptedBase64 = btoa(String.fromCharCode(...new Uint8Array(encryptedData)));
            outputText.value = encryptedBase64;
        } catch (error) {
            alert('Encryption Error: ' + error);
        }
    }

    // Decryption Function
    async function decryptCBC() {
        const inputText = document.getElementById('inputText');
        const outputText = document.getElementById('outputText');
        
        if (!key || !iv) {
            alert('Please generate a key first!');
            return;
        }
        
        try {
            const encryptedData = Uint8Array.from(atob(outputText.value), c => c.charCodeAt(0));
            const decryptedData = await window.crypto.subtle.decrypt({
                name: 'AES-CBC',
                iv: iv,
            }, key, encryptedData);
            
            const decryptedText = new TextDecoder().decode(decryptedData);
            inputText.value = decryptedText;
        } catch (error) {
            alert('Decryption Error: ' + error);
        }
    }

    // Attach event listeners
    document.getElementById('encryptBtn').addEventListener('click', encryptCBC);
    document.getElementById('decryptBtn').addEventListener('click', decryptCBC);
</script>
@endpush
@endsection