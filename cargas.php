<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Cargas</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Gestão de Cargas</h1>
        <a href="index.php" class="nav-button">Voltar ao Kanban</a>
    </header>

    <div class="main-container">
        <div class="shipping-list">
            <div id="pending-cards-container" class="pending-cards-section">
               
            </div>
        </div>
        <div class="shipping-lanes-container-wrapper">
            <h2>Destinos de Carga</h2>
            <form id="add-lane-form" class="add-lane-form">
                <h3>Criar Nova Rota</h3>
                <input type="text" id="new-lane-destination" placeholder="Nome do Destino" required>
                <input type="text" id="new-lane-driver" placeholder="Nome do Motorista" required>
                <button type="submit">Criar Rota</button>
            </form>

            <div id="shipping-lanes-container" class="shipping-lanes-container">
                
            </div>
        </div>
    </div>

    <script src="js/cargas.js"></script>
</body>
</html>
