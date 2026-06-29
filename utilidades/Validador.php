<?php

declare(strict_types=1);

class Validador
{
    private array $errores = [];

    public function limpiar(): self
    {
        $this->errores = [];
        return $this;
    }

    public function requerido(string $campo, string $valor): self
    {
        if (trim($valor) === '') {
            $this->errores[] = "El campo {$campo} es obligatorio.";
        }
        return $this;
    }

    public function email(string $campo, string $valor): self
    {
        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = "El campo {$campo} debe ser un correo válido.";
        }
        return $this;
    }

    public function contrasena(string $campo, string $valor): self
    {
        $len = mb_strlen($valor);
        if ($len === 0) {
            $this->errores[] = "El campo {$campo} es obligatorio.";
            return $this;
        }
        if ($len < 8 || $len > 12) {
            $this->errores[] = "La contraseña debe tener entre 8 y 12 caracteres.";
        }
        return $this;
    }

    public function soloLetras(string $campo, string $valor): self
    {
        if ($valor !== '' && !preg_match('/^[\p{L} ]+$/u', $valor)) {
            $this->errores[] = "El campo {$campo} solo puede contener letras y espacios.";
        }
        return $this;
    }

    public function fecha(string $campo, string $valor): self
    {
        if (trim($valor) === '') {
            $this->errores[] = "El campo {$campo} es obligatorio.";
            return $this;
        }
        if (strtotime($valor) === false) {
            $this->errores[] = "El campo {$campo} debe ser una fecha válida.";
        }
        return $this;
    }

    public function enteroPositivo(string $campo, int $valor): self
    {
        if ($valor <= 0) {
            $this->errores[] = "El campo {$campo} debe ser un número válido.";
        }
        return $this;
    }

    public function imagen(string $campo, array $archivo): self
    {
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->errores[] = "La imagen {$campo} no se pudo subir correctamente.";
            return $this;
        }

        if (($archivo['size'] ?? 0) > 2 * 1024 * 1024) {
            $this->errores[] = "La imagen {$campo} no debe superar 2 MB.";
            return $this;
        }

        $info = getimagesize($archivo['tmp_name'] ?? '');
        $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if ($info === false || !in_array($info['mime'], $permitidos, true)) {
            $this->errores[] = "La imagen {$campo} debe ser JPG, PNG o WEBP.";
        }

        return $this;
    }

    public function errores(): array
    {
        return $this->errores;
    }

    public function tieneErrores(): bool
    {
        return !empty($this->errores);
    }
}
