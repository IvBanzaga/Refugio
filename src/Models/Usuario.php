<?php
namespace Models;

use PDO;

/**
 * Modelo Usuario
 *
 * Gestiona las operaciones CRUD de usuarios
 */
class Usuario
{

    private static function getDb()
    {
        global $conexionPDO;
        return $conexionPDO;
    }

    /**
     * Obtener todos los usuarios
     *
     * @param array $filtros Filtros opcionales
     * @return array
     */
    public static function all($filtros = [])
    {
        $db     = self::getDb();
        $sql    = "SELECT * FROM usuarios WHERE 1=1";
        $params = [];

        if (isset($filtros['rol'])) {
            $sql            .= " AND rol = :rol";
            $params[':rol']  = $filtros['rol'];
        }

        if (isset($filtros['activo'])) {
            $sql               .= " AND activo = :activo";
            $params[':activo']  = $filtros['activo'];
        }

        $sql .= " ORDER BY num_socio ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar usuario por ID
     *
     * @param int $id
     * @return array|null
     */
    public static function find($id)
    {
        $db   = self::getDb();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Buscar usuario por email
     *
     * @param string $email
     * @return array|null
     */
    public static function findByEmail($email)
    {
        $db   = self::getDb();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Buscar usuario por número de socio
     *
     * @param string $numSocio
     * @return array|null
     */
    public static function findByNumSocio($numSocio)
    {
        $db   = self::getDb();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE num_socio = :num_socio");
        $stmt->bindParam(':num_socio', $numSocio);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crear nuevo usuario
     *
     * @param array $data
     * @return int|false ID del usuario creado o false
     */
    public static function create($data)
    {
        $db = self::getDb();

        $sql = "INSERT INTO usuarios (
            num_socio, nombre, apellido1, apellido2, dni, email,
            telf, password, rol, activo, foto
        ) VALUES (
            :num_socio, :nombre, :apellido1, :apellido2, :dni, :email,
            :telf, :password, :rol, :activo, :foto
        )";

        $stmt = $db->prepare($sql);

        $result = $stmt->execute([
            ':num_socio' => $data['num_socio'] ?? null,
            ':nombre'    => $data['nombre'],
            ':apellido1' => $data['apellido1'],
            ':apellido2' => $data['apellido2'] ?? '',
            ':dni'       => $data['dni'],
            ':email'     => $data['email'],
            ':telf'      => $data['telf'] ?? '',
            ':password'  => $data['password'], // Ya debe venir hasheado
            ':rol'       => $data['rol'] ?? 'user',
            ':activo'    => $data['activo'] ?? 1,
            ':foto'      => $data['foto'] ?? null,
        ]);

        return $result ? $db->lastInsertId() : false;
    }

    /**
     * Actualizar usuario
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update($id, $data)
    {
        $db = self::getDb();

        $fields = [];
        $params = [':id' => $id];

        $allowedFields = ['num_socio', 'nombre', 'apellido1', 'apellido2',
            'dni', 'email', 'telf', 'password', 'rol', 'activo', 'foto'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[]          = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql  = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Eliminar usuario
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $db   = self::getDb();
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Verificar credenciales de login
     *
     * @param string $email
     * @param string $password
     * @return array|false Usuario o false si falla
     */
    public static function authenticate($email, $password)
    {
        $usuario = self::findByEmail($email);

        if (! $usuario) {
            return false;
        }

        if (! password_verify($password, $usuario['password'])) {
            return false;
        }

        // Verificar si está activo
        if (! $usuario['activo']) {
            return false;
        }

        return $usuario;
    }

    /**
     * Contar usuarios
     *
     * @param array $filtros
     * @return int
     */
    public static function count($filtros = [])
    {
        $db     = self::getDb();
        $sql    = "SELECT COUNT(*) as total FROM usuarios WHERE 1=1";
        $params = [];

        if (isset($filtros['rol'])) {
            $sql            .= " AND rol = :rol";
            $params[':rol']  = $filtros['rol'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
