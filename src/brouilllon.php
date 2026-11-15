class StoreProductRequest extends FormRequest
{
    /**
     * Champs autorisés dans le JSON.
     * C'est cette liste qui sert de "whitelist".
     */
    protected array $allowedKeys = ['name', 'price', 'active', 'tags'];

    protected function prepareForValidation(): void
    {
        if ($this->filled('payload')) {
            $data = json_decode($this->input('payload'), true);

            // Aplatir uniquement les clés autorisées
            foreach ($this->allowedKeys as $key) {
                $this->merge([
                    "payload.{$key}" => $data[$key] ?? null,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'payload'          => ['required', 'json'],
            'payload.name'     => ['required', 'string', 'max:255'],
            'payload.price'    => ['required', 'numeric', 'min:0'],
            'payload.active'   => ['boolean'],
            'payload.tags'     => ['array', 'max:10'],
        ];
    }

    /**
     * Retourne uniquement les champs validés, décodés.
     */
    public function validatedJson(): array
    {
        $validated = $this->validated();

        // Extraire et reconstruire l'objet JSON nettoyé
        $result = [];
        foreach ($this->allowedKeys as $key) {
            if (array_key_exists("payload.{$key}", $validated)) {
                $result[$key] = $validated["payload.{$key}"];
            }
        }

        return $result;
    }
}
Utilisation dans le controller :

php
Copier
public function store(StoreProductRequest $request)
{
    // Retourne: ['name' => '...', 'price' => 9.99, 'active' => true, 'tags' => [...]]
    // Les clés non autorisées ou invalides sont absentes.
    $data = $request->validatedJson();

    Product::create($data);
}


// app/Http/Requests/StoreProductRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Champs autorisés dans le JSON.
     * C'est cette liste qui sert de "whitelist".
     */
    protected array $allowedKeys = ['name', 'price', 'active', 'tags'];

    protected function prepareForValidation(): void
    {
        if ($this->filled('payload')) {
            $data = json_decode($this->input('payload'), true);

            // Aplatir uniquement les clés autorisées
            foreach ($this->allowedKeys as $key) {
                $this->merge([
                    "payload.{$key}" => $data[$key] ?? null,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'payload'          => ['required', 'json'],
            'payload.name'     => ['required', 'string', 'max:255'],
            'payload.price'    => ['required', 'numeric', 'min:0'],
            'payload.active'   => ['boolean'],
            'payload.tags'     => ['array', 'max:10'],
        ];
    }

    /**
     * Retourne uniquement les champs validés, décodés.
     */
    public function validatedJson(): array
    {
        $validated = $this->validated();

        // Extraire et reconstruire l'objet JSON nettoyé
        $result = [];
        foreach ($this->allowedKeys as $key) {
            if (array_key_exists("payload.{$key}", $validated)) {
                $result[$key] = $validated["payload.{$key}"];
            }
        }

        return $result;
    }
}