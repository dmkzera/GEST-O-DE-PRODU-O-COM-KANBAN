<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quadro Kanban - Gestão de Produção</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header>
        <h1>Chão de Fábrica - Fluxo de Produção</h1>
        <a href="cargas.php" class="nav-button">Gestão de Cargas 🚚</a>
    </header>

    <main class="kanban-board">
        <div class="kanban-column" id="todo" data-status="todo">
            <h2>A Fazer</h2>
            <div class="cards-container"></div>
        </div>
        <div class="kanban-column" id="doing" data-status="doing">
            <h2>Em Produção</h2>
            <div class="cards-container"></div>
        </div>
        <div class="kanban-column" id="done" data-status="done">
            <h2>Concluído</h2>
            <div class="cards-container"></div>
        </div>
    </main>

    <div class="modal" id="add-card-modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Criar Novo Pedido</h2>
            <form id="add-card-form">
                <input type="text" id="card-title" placeholder="Título do Pedido" required>
                <textarea id="card-description" placeholder="Descrição detalhada..."></textarea>
                <button type="submit">Adicionar Pedido</button>
            </form>
        </div>
    </div>
    <div class="modal" id="edit-card-modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Editar Pedido</h2>
            <form id="edit-card-form">
                <input type="hidden" id="edit-card-id">
                <input type="text" id="edit-card-title" placeholder="Título do Pedido" required>
                <textarea id="edit-card-description" placeholder="Descrição detalhada..."></textarea>
                <button type="submit">Salvar Alterações</button>
            </form>
        </div>
    </div>
    <div class="modal" id="lamination-modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Detalhes da Laminação</h2>
            <form id="lamination-form">
                <input type="hidden" id="lamination-card-id">
                <label for="laminator-name">Laminador:</label>
                <input type="text" id="laminator-name" required>

                <label for="resin-consumed">Resina Consumida (kg):</label>
                <input type="number" step="0.01" id="resin-consumed" required>

                <label for="fiber-consumed">Fibra Consumida (m²):</label>
                <input type="number" step="0.01" id="fiber-consumed" required>

                <label for="identification-number">Número de Identificação:</label>
                <input type="text" id="identification-number" required>
                
                <button type="submit">Salvar Detalhes</button>
            </form>
        </div>
    </div>

    <button id="add-card-btn">+</button>


    <script src="js/script.js"></script>
</body>
</html>
