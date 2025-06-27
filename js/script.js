document.addEventListener('DOMContentLoaded', () => {
    const kanbanBoard = document.querySelector('.kanban-board');
    let cardsState = [];

    const addModal = document.getElementById('add-card-modal');
    const addForm = document.getElementById('add-card-form');
    const editModal = document.getElementById('edit-card-modal');
    const editForm = document.getElementById('edit-card-form');
    const laminationModal = document.getElementById('lamination-modal');
    const laminationForm = document.getElementById('lamination-form');
    const addCardBtn = document.getElementById('add-card-btn');

    async function postData(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return response.json();
    }

    function smartRender(freshCards) {
        const freshCardMap = new Map(freshCards.map(c => [c.id.toString(), c]));
        const oldCardMap = new Map(cardsState.map(c => [c.id.toString(), c]));

        oldCardMap.forEach((oldCard, id) => {
            if (!freshCardMap.has(id)) {
                document.querySelector(`.card[data-id='${id}']`)?.remove();
            }
        });

        freshCardMap.forEach((freshCard, id) => {
            let element = document.querySelector(`.card[data-id='${id}']`);
            if (!element) {
                element = createCardElement(freshCard);
                document.querySelector(`#${freshCard.status} .cards-container`).appendChild(element);
            } else {
                if (oldCardMap.get(id)?.title !== freshCard.title || oldCardMap.get(id)?.description !== freshCard.description) {
                    element.querySelector('h3').textContent = freshCard.title;
                    element.querySelector('p').textContent = freshCard.description || '';
                }
                if (oldCardMap.get(id)?.status !== freshCard.status) {
                    document.querySelector(`#${freshCard.status} .cards-container`).appendChild(element);
                }
            }
            renderProductionDetails(element, freshCard);
            renderCardFooter(element, freshCard);
        });

        cardsState = freshCards;
        addDragAndDropListeners();
    }

    function createCardElement({ id, title, description }) {
        const card = document.createElement('div');
        card.className = 'card';
        card.setAttribute('draggable', 'true');
        card.dataset.id = id;
        card.innerHTML = `
            <h3>${title}</h3>
            <p>${description || ''}</p>
            <div class="production-steps"></div>
            <div class="card-footer"></div>
            <div class="card-actions">
                <button class="card-action-btn edit-btn" title="Editar">✏️</button>
                <button class="card-action-btn delete-btn" title="Deletar">🗑️</button>
            </div>
        `;
        return card;
    }

    function renderProductionDetails(cardElement, card) {
        const container = cardElement.querySelector('.production-steps');
        if (card.status !== 'doing' && card.status !== 'done') {
            container.style.display = 'none';
            return;
        }
        container.style.display = 'block';
        const isDisabled = card.status === 'done';
        const steps = ['pintura', 'laminação', 'pastilhamento'];
        let savedSteps = {};
        try {
            if (card.production_steps && typeof card.production_steps === 'string') {
                savedSteps = JSON.parse(card.production_steps);
            }
        } catch (e) { console.error('Erro JSON:', e); }

        container.innerHTML = steps.map(step => `
            <label style="${isDisabled ? 'cursor: not-allowed; color: #7f8c8d;' : ''}">
                <input type="checkbox" class="production-step-check" data-step="${step}" 
                       ${savedSteps[step] ? 'checked' : ''} ${isDisabled ? 'disabled' : ''}>
                ${step.charAt(0).toUpperCase() + step.slice(1)}
            </label>
        `).join('');
    }

    function renderCardFooter(cardElement, card) {
        const footer = cardElement.querySelector('.card-footer');
        footer.innerHTML = '';
        if (card.status === 'done' && card.shipping_status === 'none') {
            footer.innerHTML = `<button class="shipping-btn" data-card-id="${card.id}">Enviar para Cargas ➡️</button>`;
        } else if (card.status === 'done' && card.shipping_status === 'pending') {
            footer.innerHTML = `<button class="shipping-btn" disabled>Aguardando Envio</button>`;
        }
    }

    function addDragAndDropListeners() {
        document.querySelectorAll('.card').forEach(card => {
            card.draggable = true;
            card.addEventListener('dragstart', () => card.classList.add('dragging'), { once: true });
            card.addEventListener('dragend', () => card.classList.remove('dragging'), { once: true });
        });
        document.querySelectorAll('.cards-container').forEach(container => {
            container.addEventListener('dragover', e => {
                e.preventDefault();
                container.parentElement.classList.add('drag-over');
            });
            container.addEventListener('dragleave', () => container.parentElement.classList.remove('drag-over'));
            container.addEventListener('drop', async e => {
                e.preventDefault();
                container.parentElement.classList.remove('drag-over');
                const draggingCard = document.querySelector('.dragging');
                if (draggingCard && draggingCard.parentElement !== container) {
                    container.appendChild(draggingCard);
                    const cardId = draggingCard.dataset.id;
                    const newStatus = container.parentElement.dataset.status;
                    await postData('api/update_card_status.php', { id: cardId, status: newStatus });
                    fetchAndSync();
                }
            });
        });
    }

    function setupModals() {
        const allModals = document.querySelectorAll('.modal');
        addCardBtn.addEventListener('click', () => addModal.style.display = 'block');
        allModals.forEach(modal => {
            modal.querySelector('.close-button').addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
        });
        addForm.addEventListener('submit', async e => {
            e.preventDefault();
            await postData('api/create_card.php', { title: addForm.querySelector('#card-title').value, description: addForm.querySelector('#card-description').value });
            addForm.reset(); addModal.style.display = 'none'; fetchAndSync();
        });
        editForm.addEventListener('submit', async e => {
            e.preventDefault();
            await postData('api/edit_card.php', { id: editForm.querySelector('#edit-card-id').value, title: editForm.querySelector('#edit-card-title').value, description: editForm.querySelector('#edit-card-description').value });
            editModal.style.display = 'none'; fetchAndSync();
        });
        laminationForm.addEventListener('submit', async e => {
            e.preventDefault();
            const cardId = laminationForm.querySelector('#lamination-card-id').value;
            const details = { laminador: laminationForm.querySelector('#laminator-name').value, resina_consumida: laminationForm.querySelector('#resin-consumed').value, fibra_consumida: laminationForm.querySelector('#fiber-consumed').value, numero_identificacao: laminationForm.querySelector('#identification-number').value };
            await postData('api/update_production_details.php', { id: cardId, lamination_details: details });
            laminationModal.style.display = 'none'; fetchAndSync();
        });
    }

    kanbanBoard.addEventListener('click', async e => {
        const cardElement = e.target.closest('.card');
        if (!cardElement) return;
        const cardId = cardElement.dataset.id;
        const cardData = cardsState.find(c => c.id.toString() === cardId);

        if (e.target.classList.contains('edit-btn')) {
            editForm.querySelector('#edit-card-id').value = cardData.id;
            editForm.querySelector('#edit-card-title').value = cardData.title;
            editForm.querySelector('#edit-card-description').value = cardData.description;
            editModal.style.display = 'block';
        } else if (e.target.classList.contains('delete-btn')) {
            if (confirm('Tem certeza?')) {
                await postData('api/delete_card.php', { id: cardId });
                fetchAndSync();
            }
        } else if (e.target.classList.contains('production-step-check')) {
            const checkbox = e.target;
            const stepName = checkbox.dataset.step;
            let savedSteps = cardData.production_steps ? JSON.parse(cardData.production_steps) : {};
            savedSteps[stepName] = checkbox.checked;
            await postData('api/update_production_details.php', { id: cardId, production_steps: savedSteps });
            if (stepName === 'laminação' && checkbox.checked) {
                const laminationDetails = cardData.lamination_details ? JSON.parse(cardData.lamination_details) : {};
                laminationForm.querySelector('#lamination-card-id').value = cardId;
                laminationForm.querySelector('#laminator-name').value = laminationDetails.laminador || '';
                laminationForm.querySelector('#resin-consumed').value = laminationDetails.resina_consumida || '';
                laminationForm.querySelector('#fiber-consumed').value = laminationDetails.fibra_consumida || '';
                laminationForm.querySelector('#identification-number').value = laminationDetails.numero_identificacao || '';
                laminationModal.style.display = 'block';
            } else {
                fetchAndSync();
            }
        } else if (e.target.classList.contains('shipping-btn')) {
            e.target.disabled = true;
            e.target.textContent = 'Enviando...';
            await postData('api/send_to_shipping.php', { id: cardId });
            fetchAndSync();
        }
    });

    async function fetchAndSync() {
        const response = await fetch('api/get_cards.php');
        const freshCards = await response.json();
        smartRender(freshCards);
    }

    async function init() {
        setupModals();
        await fetchAndSync();
        setInterval(fetchAndSync, 5000);
    }
    init();
});
