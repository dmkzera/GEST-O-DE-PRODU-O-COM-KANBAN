document.addEventListener('DOMContentLoaded', () => {
    const pendingCardsContainer = document.getElementById('pending-cards-container');
    const shippingLanesContainer = document.getElementById('shipping-lanes-container');

    // --- FUNÇÕES DE API ---
    async function postData(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Erro desconhecido.' }));
            throw new Error(errorData.message);
        }
        return response.json();
    }

    async function fetchData() {
        try {
            const response = await fetch('api/get_shipping_data.php');
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            renderData(data);
        } catch (error) {
            console.error('Erro ao buscar dados de envio:', error);
            pendingCardsContainer.innerHTML = `<p>Erro ao carregar dados: ${error.message}</p>`;
        }
    }

    // --- FUNÇÕES DE RENDERIZAÇÃO ---
    function createCardElement(card) {
        const cardEl = document.createElement('div');
        cardEl.className = 'shipping-card';
        cardEl.dataset.id = card.id;
        cardEl.setAttribute('draggable', true);
        cardEl.innerHTML = `<strong>${card.title}</strong><p>${card.description || ''}</p>`;
        return cardEl;
    }

    function createLaneElement(lane) {
        const laneEl = document.createElement('div');
        laneEl.className = 'shipping-lane';
        laneEl.dataset.laneId = lane.id;
        laneEl.innerHTML = `
            <div class="lane-header">
                <div class="lane-info">
                    <h3>${lane.name}</h3>
                    <span>(Motorista: ${lane.driver})</span>
                </div>
                <div class="lane-actions">
                    <button class="edit-lane-btn" title="Editar Rota">✏️</button>
                    <button class="delete-lane-btn" title="Excluir Rota">🗑️</button>
                </div>
            </div>
            <div class="lane-edit-form" style="display: none;">
                <input type="text" class="edit-destination-input" value="${lane.name}" required>
                <input type="text" class="edit-driver-input" value="${lane.driver}" required>
                <button class="save-lane-btn">Salvar</button>
                <button class="cancel-edit-btn">Cancelar</button>
            </div>
            <div class="lane-cards-container" data-lane-id="${lane.id}"></div>
        `;
        return laneEl;
    }

    function renderData(data) {
        pendingCardsContainer.innerHTML = '<h2>Pedidos Prontos para Envio</h2>';
        if (data.cards.pending && data.cards.pending.length > 0) {
            data.cards.pending.forEach(card => pendingCardsContainer.appendChild(createCardElement(card)));
        } else {
            pendingCardsContainer.innerHTML += '<p>Nenhum pedido pendente.</p>';
        }

        shippingLanesContainer.innerHTML = '<h2>Rotas de Envio</h2>';
        data.lanes.forEach(lane => {
            const laneEl = createLaneElement(lane);
            const laneCardsContainer = laneEl.querySelector('.lane-cards-container');
            if (data.cards.assigned[lane.id]) {
                data.cards.assigned[lane.id].forEach(card => laneCardsContainer.appendChild(createCardElement(card)));
            }
            shippingLanesContainer.appendChild(laneEl);
        });

        addDragAndDropListeners();
    }

    // --- LISTENERS DE EVENTOS ---
    function addDragAndDropListeners() {
        const cards = document.querySelectorAll('.shipping-card');
        // O alvo agora é a rota inteira, não apenas o contêiner de cards.
        const lanes = document.querySelectorAll('.shipping-lane');
        let draggedCard = null;

        cards.forEach(card => {
            card.addEventListener('dragstart', e => {
                draggedCard = e.target;
                setTimeout(() => { if (e.target) e.target.style.opacity = '0.5'; }, 0);
            });
            card.addEventListener('dragend', e => {
                if (e.target) e.target.style.opacity = '1';
                draggedCard = null;
            });
        });

        // Agora, 'lane' é o elemento .shipping-lane completo.
        lanes.forEach(lane => {
            lane.addEventListener('dragover', e => {
                e.preventDefault();
                // Adiciona a classe de feedback visual na própria rota.
                lane.classList.add('drag-over');
            });
            lane.addEventListener('dragleave', () => {
                lane.classList.remove('drag-over');
            });
            lane.addEventListener('drop', async e => {
                e.preventDefault();
                lane.classList.remove('drag-over');
                if (!draggedCard) return;

                // O ID da rota é pego diretamente do elemento da rota.
                const cardId = draggedCard.dataset.id;
                const laneId = lane.dataset.laneId;

                try {
                    await postData('api/assign_card_to_lane.php', { card_id: cardId, lane_id: laneId });
                    fetchData(); // Recarrega para mostrar o resultado.
                } catch (error) {
                    alert(`Erro ao atribuir card: ${error.message}`);
                    fetchData();
                }
            });
        });
    }

    function setupFormAndActionListeners() {
        // Formulário para ADICIONAR rota
        const form = document.getElementById('add-lane-form');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const destInput = document.getElementById('new-lane-destination');
            const driverInput = document.getElementById('new-lane-driver');
            try {
                await postData('api/create_shipping_lane.php', { 
                    destination: destInput.value.trim(), 
                    driver: driverInput.value.trim() 
                });
                destInput.value = '';
                driverInput.value = '';
                fetchData();
            } catch (error) {
                alert(`Erro ao criar rota: ${error.message}`);
            }
        });

        // Ações de EDITAR e EXCLUIR rota (usando delegação de eventos)
        shippingLanesContainer.addEventListener('click', async (e) => {
            const laneEl = e.target.closest('.shipping-lane');
            if (!laneEl) return;

            const laneId = laneEl.dataset.laneId;
            const header = laneEl.querySelector('.lane-header');
            const form = laneEl.querySelector('.lane-edit-form');

            if (e.target.classList.contains('edit-lane-btn')) {
                header.style.display = 'none';
                form.style.display = 'block';
            }

            if (e.target.classList.contains('cancel-edit-btn')) {
                header.style.display = '';
                form.style.display = 'none';
            }

            if (e.target.classList.contains('save-lane-btn')) {
                const destInput = form.querySelector('.edit-destination-input');
                const driverInput = form.querySelector('.edit-driver-input');
                try {
                    await postData('api/update_shipping_lane.php', {
                        lane_id: laneId,
                        destination: destInput.value.trim(),
                        driver: driverInput.value.trim()
                    });
                    fetchData();
                } catch (error) {
                    alert(`Erro ao salvar rota: ${error.message}`);
                }
            }

            if (e.target.classList.contains('delete-lane-btn')) {
                if (confirm('Tem certeza que deseja excluir esta rota? Pedidos atribuídos não serão excluídos, mas a rota sim.')) {
                    try {
                        await postData('api/delete_shipping_lane.php', { lane_id: laneId });
                        fetchData();
                    } catch (error) {
                        alert(`Erro ao excluir rota: ${error.message}`);
                    }
                }
            }
        });
    }

    // --- INICIALIZAÇÃO ---
    setupFormAndActionListeners();
    fetchData();
});
