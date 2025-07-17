<?php
// Configuração do banco de dados
$host = 'localhost';
$dbname = 'laravel_beta2';
$username = 'root';
$password = '';

try {
    // Conectar ao banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consultar perfis
    $perfis = $pdo->query("SELECT * FROM profiles")->fetchAll(PDO::FETCH_ASSOC);
    
    // Consultar permissões
    $permissoes = $pdo->query("SELECT * FROM permissions")->fetchAll(PDO::FETCH_ASSOC);
    
    // Consultar relacionamentos entre perfis e permissões
    $relacionamentos = $pdo->query("SELECT * FROM profile_permissions")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Perfis e Permissões</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .container { margin-bottom: 40px; }
    </style>
</head>
<body>
    <h1>Perfis e Permissões do Sistema</h1>
    
    <div class="container">
        <h2>Perfis</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($perfis as $perfil): ?>
                <tr>
                    <td><?= $perfil['id'] ?></td>
                    <td><?= $perfil['name'] ?></td>
                    <td><?= $perfil['description'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="container">
        <h2>Permissões</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Código</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissoes as $permissao): ?>
                <tr>
                    <td><?= $permissao['id'] ?></td>
                    <td><?= $permissao['name'] ?></td>
                    <td><?= $permissao['code'] ?></td>
                    <td><?= $permissao['description'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="container">
        <h2>Relacionamentos entre Perfis e Permissões</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID do Perfil</th>
                    <th>ID da Permissão</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($relacionamentos as $rel): ?>
                <tr>
                    <td><?= $rel['id'] ?></td>
                    <td><?= $rel['profile_id'] ?></td>
                    <td><?= $rel['permission_id'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="container">
        <h2>Permissões por Perfil</h2>
        <?php foreach ($perfis as $perfil): ?>
            <h3>Perfil: <?= $perfil['name'] ?> (ID: <?= $perfil['id'] ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID da Permissão</th>
                        <th>Nome da Permissão</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $perfilPermissoes = [];
                    foreach ($relacionamentos as $rel) {
                        if ($rel['profile_id'] == $perfil['id']) {
                            foreach ($permissoes as $permissao) {
                                if ($permissao['id'] == $rel['permission_id']) {
                                    $perfilPermissoes[] = $permissao;
                                }
                            }
                        }
                    }
                    
                    if (empty($perfilPermissoes)): ?>
                        <tr>
                            <td colspan="3">Nenhuma permissão encontrada para este perfil</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($perfilPermissoes as $permissao): ?>
                        <tr>
                            <td><?= $permissao['id'] ?></td>
                            <td><?= $permissao['name'] ?></td>
                            <td><?= $permissao['description'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>
</body>
</html> 