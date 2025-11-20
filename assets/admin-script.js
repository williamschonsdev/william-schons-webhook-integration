function toggleEventCheckbox(eventKey) {
    const checkbox = document.getElementById('event_' + eventKey);
    const card = checkbox.closest('.n8nwoo-event-item');
    checkbox.checked = !checkbox.checked;
    card.classList.toggle('active', checkbox.checked);
    
    const webhookField = card.querySelector('.n8nwoo-webhook-field');
    if (checkbox.checked) {
        if (!webhookField) {
            const newField = document.createElement('div');
            newField.className = 'n8nwoo-webhook-field';
            newField.innerHTML = '<input type="url" name="n8nwoo_individual_webhooks[' + eventKey + ']" value="" placeholder="Webhook específico ou deixe vazio para usar o principal" class="n8nwoo-webhook-individual-input" />';
            card.appendChild(newField);
        }
    } else {
        if (webhookField) {
            webhookField.remove();
        }
    }
}

function toggleStatusCheckbox(statusKey) {
    const checkbox = document.getElementById('status_' + statusKey);
    const card = checkbox.closest('.n8nwoo-status-item');
    checkbox.checked = !checkbox.checked;
    card.classList.toggle('active', checkbox.checked);
}

function testWebhook() {
    const webhookUrl = document.getElementById('n8nwoo_webhook_url').value;
    const testButton = event.target;
    
    if (!webhookUrl) {
        alert('Por favor, insira uma URL de webhook primeiro.');
        return;
    }
    
    testButton.disabled = true;
    testButton.textContent = 'Enviando...';
    
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=n8nwoo_test_webhook&webhook_url=' + encodeURIComponent(webhookUrl)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Webhook testado com sucesso!\n\n' + data.data.message);
        } else {
            alert('❌ Erro ao testar webhook:\n\n' + data.data);
        }
    })
    .catch(error => {
        alert('❌ Erro ao testar webhook:\n\n' + error.message);
    })
    .finally(() => {
        testButton.disabled = false;
        testButton.textContent = '🧪 Testar Webhook';
    });
}
