<?php

declare(strict_types=1);

class SolicitudModel implements IRepositorio
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->getConexion();
    }

    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array
    {
        $offset = ($pagina - 1) * POR_PAGINA;
        $like = "%$busqueda%";

        $stmt = $this->pdo->prepare(
            'SELECT
                s.*,
                CASE
                    WHEN s.id_profesor IS NOT NULL THEN CONCAT(p.primer_nombre, " ", p.primer_apellido)
                    ELSE CONCAT(e.primer_nombre, " ", e.primer_apellido)
                END AS estudiante,
                COALESCE(e.cip, p.cip) AS cip,
                CASE
                    WHEN s.id_profesor IS NOT NULL THEN "Docente"
                    ELSE "Estudiante"
                END AS tipo_solicitante
             FROM solicitudes_libros s
             LEFT JOIN estudiantes e ON e.id_estudiante = s.id_estudiante
             LEFT JOIN profesores p ON p.id_profesor = s.id_profesor
             WHERE
                s.titulo LIKE ?
                OR s.autor LIKE ?
                OR s.materia LIKE ?
                OR s.motivo LIKE ?
                OR e.primer_nombre LIKE ?
                OR e.primer_apellido LIKE ?
                OR e.cip LIKE ?
                OR p.primer_nombre LIKE ?
                OR p.primer_apellido LIKE ?
                OR p.cip LIKE ?
             ORDER BY s.created_at DESC
             LIMIT ? OFFSET ?'
        );

        $stmt->execute([
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            POR_PAGINA,
            $offset
        ]);

        return $stmt->fetchAll();
    }

    public function obtenerPorLector(string $tipo, int $id): array
    {
        if ($tipo === 'PROFESOR') {
            $stmt = $this->pdo->prepare(
                'SELECT *
                 FROM solicitudes_libros
                 WHERE id_profesor = ?
                 ORDER BY created_at DESC'
            );
            $stmt->execute([$id]);
            return $stmt->fetchAll();
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM solicitudes_libros
             WHERE id_estudiante = ?
             ORDER BY created_at DESC'
        );
        $stmt->execute([$id]);

        return $stmt->fetchAll();
    }

    public function obtenerPorEstudiante(int $idEstudiante): array
    {
        return $this->obtenerPorLector('ESTUDIANTE', $idEstudiante);
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
             FROM solicitudes_libros
             WHERE id_solicitud = ?'
        );

        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO solicitudes_libros (
                id_estudiante,
                id_profesor,
                titulo,
                autor,
                area,
                materia,
                motivo,
                tipo_solicitud,
                institucion_sugerida,
                descripcion
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $ok = $stmt->execute([
            $d['id_estudiante'] ?? null,
            $d['id_profesor'] ?? null,
            $d['titulo'],
            $d['autor'] ?: null,
            $d['area'],
            $d['materia'] ?: null,
            $d['motivo'] ?: null,
            $d['tipo_solicitud'] ?: 'COMPRA',
            $d['institucion_sugerida'] ?: null,
            $d['descripcion'] ?: null,
        ]);

        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE solicitudes_libros
             SET estado = ?, observaciones = ?
             WHERE id_solicitud = ?'
        );

        return $stmt->execute([
            $d['estado'],
            $d['observaciones'] ?: null,
            $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        return false;
    }

    public function contarTotal(string $busqueda = ''): int
    {
        $like = "%$busqueda%";

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM solicitudes_libros s
             LEFT JOIN estudiantes e ON e.id_estudiante = s.id_estudiante
             LEFT JOIN profesores p ON p.id_profesor = s.id_profesor
             WHERE
                s.titulo LIKE ?
                OR s.autor LIKE ?
                OR s.materia LIKE ?
                OR s.motivo LIKE ?
                OR e.primer_nombre LIKE ?
                OR e.primer_apellido LIKE ?
                OR e.cip LIKE ?
                OR p.primer_nombre LIKE ?
                OR p.primer_apellido LIKE ?
                OR p.cip LIKE ?'
        );

        $stmt->execute([
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like
        ]);

        return (int)$stmt->fetchColumn();
    }
}