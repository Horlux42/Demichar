const XP_PER_LEVEL = 300;
const characterId = document.body.dataset.characterId;

let state = {
    editing: document.body.dataset.mode === 'edit',
    level: parseInt(document.body.dataset.level),
    xp: parseInt(document.body.dataset.xp),
    coins: parseInt(document.body.dataset.coins),
    hp: parseInt(document.body.dataset.hp),
    maxHp: parseInt(document.body.dataset.maxhp),
    modules: JSON.parse(document.body.dataset.modules || '[]')
};

let addCardElement = null;

const editBtn = document.getElementById('editBtn');
const modeBadge = document.getElementById('modeBadge');
const charName = document.getElementById('charName');
const levelLabel = document.getElementById('levelLabel');
const xpBar = document.getElementById('xpBar');
const xpTooltip = document.getElementById('xpTooltip');
const modulesArea = document.getElementById('modulesArea');
const levelupMsg = document.getElementById('levelupMsg');
const coinCountSpan = document.getElementById('coinCount');
const hpValSpan = document.getElementById('hpVal');
const hpMaxSpan = document.getElementById('hpMax');

// Модалки
const coinModal = document.getElementById('coinModal');
const coinInput = document.getElementById('coinInput');
const hpModal = document.getElementById('hpModal');
const hpValueInput = document.getElementById('hpValueInput');
const hpMaxInput = document.getElementById('hpMaxInput');

// Делаем state глобальным
window.state = state;

async function saveToServer() {
    const data = {
        level: state.level,
        xp: state.xp,
        coins: state.coins,
        hp: state.hp,
        maxHp: state.maxHp,
        modules: state.modules
    };
    const formData = new URLSearchParams();
    formData.append('action', 'save');
    formData.append('data', JSON.stringify(data));
    formData.append('name', charName.value);
    await fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
    });
}

function updateXPDisplay() {
    const pct = Math.min((state.xp / XP_PER_LEVEL) * 100, 100);
    if (xpBar) xpBar.style.width = pct + '%';
    if (levelLabel) levelLabel.textContent = 'Уровень ' + state.level;
    if (xpTooltip) xpTooltip.textContent = state.xp + ' / 300 XP';
}

function updateUI() {
    // Прямое обновление элементов
    const coinSpan = document.getElementById('coinCount');
    const hpSpan = document.getElementById('hpVal');
    const hpMaxSpanEl = document.getElementById('hpMax');
    
    if (coinSpan) coinSpan.innerText = state.coins;
    if (hpSpan) hpSpan.innerText = state.hp;
    if (hpMaxSpanEl) hpMaxSpanEl.innerText = state.maxHp;
    
    updateXPDisplay();
}

function showLevelUp() {
    levelupMsg.textContent = '⬆ УРОВЕНЬ ' + state.level + '!';
    levelupMsg.style.display = 'block';
    setTimeout(() => { levelupMsg.style.display = 'none'; }, 2000);
}

window.addXP = function(amount) {
    if (amount <= 0) return;
    state.xp += amount;
    let leveled = false;
    while (state.xp >= XP_PER_LEVEL) {
        state.xp -= XP_PER_LEVEL;
        state.level++;
        leveled = true;
    }
    if (leveled) showLevelUp();
    updateXPDisplay();
    saveToServer();
};

function renderModules() {
    modulesArea.querySelectorAll('.module').forEach(m => m.remove());
    if (addCardElement) addCardElement.remove();
    
    state.modules.forEach((mod, modIndex) => {
        const moduleDiv = document.createElement('div');
        moduleDiv.className = 'module';
        
        const header = document.createElement('div');
        header.className = 'module-header';
        
        const titleInput = document.createElement('input');
        titleInput.type = 'text';
        titleInput.className = 'module-title';
        titleInput.value = mod.title || 'Новый модуль';
        titleInput.placeholder = 'Название модуля';
        if (!state.editing) titleInput.setAttribute('readonly', '');
        titleInput.onchange = () => {
            state.modules[modIndex].title = titleInput.value;
            saveToServer();
        };
        header.appendChild(titleInput);
        
        const delModuleBtn = document.createElement('button');
        delModuleBtn.className = 'module-del';
        delModuleBtn.textContent = '✕';
        if (state.editing) {
            delModuleBtn.onclick = () => {
                state.modules.splice(modIndex, 1);
                renderModules();
                saveToServer();
            };
        } else {
            delModuleBtn.style.display = 'none';
        }
        header.appendChild(delModuleBtn);
        moduleDiv.appendChild(header);
        
        const itemsList = document.createElement('div');
        itemsList.className = 'items-list';
        
        const renderItems = () => {
            itemsList.innerHTML = '';
            const items = state.modules[modIndex].items || [];
            items.forEach((item, itemIndex) => {
                const row = document.createElement('div');
                row.className = 'item-row';
                
                const nameInput = document.createElement('input');
                nameInput.type = 'text';
                nameInput.className = 'item-name';
                nameInput.value = item.name || '';
                nameInput.placeholder = 'Название';
                if (!state.editing) nameInput.setAttribute('readonly', '');
                nameInput.onchange = () => {
                    state.modules[modIndex].items[itemIndex].name = nameInput.value;
                    saveToServer();
                };
                row.appendChild(nameInput);
                
                if (item.hasValue) {
                    const valueInput = document.createElement('input');
                    valueInput.type = 'number';
                    valueInput.step = 'any';
                    valueInput.className = 'item-value';
                    valueInput.value = item.value !== undefined ? item.value : '';
                    valueInput.placeholder = '0';
                    if (!state.editing) valueInput.setAttribute('readonly', '');
                    valueInput.onchange = () => {
                        state.modules[modIndex].items[itemIndex].value = valueInput.value;
                        saveToServer();
                    };
                    row.appendChild(valueInput);
                }
                
                if (state.editing) {
                    if (!item.hasValue) {
                        const addValueBtn = document.createElement('button');
                        addValueBtn.className = 'item-add-value';
                        addValueBtn.textContent = '+';
                        addValueBtn.title = 'Добавить числовое значение';
                        addValueBtn.onclick = () => {
                            state.modules[modIndex].items[itemIndex].hasValue = true;
                            state.modules[modIndex].items[itemIndex].value = '';
                            renderModules();
                            saveToServer();
                        };
                        row.appendChild(addValueBtn);
                    }
                    
                    const delItemBtn = document.createElement('button');
                    delItemBtn.className = 'item-delete';
                    delItemBtn.textContent = '✕';
                    delItemBtn.onclick = () => {
                        state.modules[modIndex].items.splice(itemIndex, 1);
                        renderModules();
                        saveToServer();
                    };
                    row.appendChild(delItemBtn);
                }
                
                itemsList.appendChild(row);
            });
        };
        
        renderItems();
        moduleDiv.appendChild(itemsList);
        
        if (state.editing) {
            const addItemBtn = document.createElement('button');
            addItemBtn.className = 'add-item-btn';
            addItemBtn.textContent = '+ Добавить строку';
            addItemBtn.onclick = () => {
                if (!state.modules[modIndex].items) state.modules[modIndex].items = [];
                state.modules[modIndex].items.push({ name: '', hasValue: false });
                renderModules();
                saveToServer();
            };
            moduleDiv.appendChild(addItemBtn);
        }
        
        modulesArea.appendChild(moduleDiv);
    });
    
    if (state.editing) {
        addCardElement = document.createElement('div');
        addCardElement.className = 'add-card';
        addCardElement.textContent = '+';
        addCardElement.onclick = () => {
            state.modules.push({ title: 'Новый модуль', items: [] });
            renderModules();
            saveToServer();
        };
        modulesArea.appendChild(addCardElement);
    }
}

function setMode(editing) {
    state.editing = editing;
    if (editing) {
        editBtn.textContent = 'Просмотр';
        editBtn.classList.remove('viewing');
        modeBadge.textContent = 'РЕДАКТИРОВАНИЕ';
        modeBadge.classList.add('edit');
        charName.removeAttribute('readonly');
    } else {
        editBtn.textContent = 'Редактировать';
        editBtn.classList.add('viewing');
        modeBadge.textContent = 'ПРОСМОТР';
        modeBadge.classList.remove('edit');
        charName.setAttribute('readonly', '');
    }
    renderModules();
    toggleAvatarButtons();
}

function toggleAvatarButtons() {
    const uploadBtn = document.getElementById('uploadAvatarBtn');
    const deleteBtn = document.getElementById('deleteAvatarBtn');
    if (!uploadBtn) return;
    
    if (state.editing) {
        uploadBtn.style.display = 'flex';
        if (deleteBtn) deleteBtn.style.display = 'flex';
    } else {
        uploadBtn.style.display = 'none';
        if (deleteBtn) deleteBtn.style.display = 'none';
    }
}

if (editBtn) editBtn.addEventListener('click', () => setMode(!state.editing));
if (charName) charName.addEventListener('change', () => saveToServer());

// ===== КУБИКИ =====
const diceBtn = document.getElementById('diceBtn');
if (diceBtn) {
    const newDiceBtn = diceBtn.cloneNode(true);
    diceBtn.parentNode.replaceChild(newDiceBtn, diceBtn);
    newDiceBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const menu = document.getElementById('diceMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
}

document.querySelectorAll('.dice-option').forEach(opt => {
    const newOpt = opt.cloneNode(true);
    opt.parentNode.replaceChild(newOpt, opt);
    newOpt.addEventListener('click', (e) => {
        e.stopPropagation();
        const val = parseInt(newOpt.dataset.dice);
        const roll = Math.ceil(Math.random() * val);
        document.getElementById('diceNum').textContent = roll;
        document.getElementById('diceLabel').textContent = `d${val}`;
        const dr = document.getElementById('diceResult');
        dr.className = 'dice-result';
        if (roll === val) dr.classList.add('crit');
        else if (roll === 1) dr.classList.add('fail');
        dr.style.display = 'block';
        setTimeout(() => dr.style.display = 'none', 2500);
        document.getElementById('diceMenu').style.display = 'none';
    });
});

// ===== ДЕНЬГИ =====
const coinStat = document.getElementById('coinStat');
if (coinStat) {
    const newCoinStat = coinStat.cloneNode(true);
    coinStat.parentNode.replaceChild(newCoinStat, coinStat);
    newCoinStat.addEventListener('click', () => {
        coinInput.value = state.coins;
        coinModal.style.display = 'flex';
    });
}

document.getElementById('coinSaveBtn')?.addEventListener('click', () => {
    const newVal = parseFloat(coinInput.value);
    if (!isNaN(newVal)) {
        state.coins = newVal;
        updateUI();
        saveToServer();
    }
    coinModal.style.display = 'none';
});
document.getElementById('coinCloseBtn')?.addEventListener('click', () => coinModal.style.display = 'none');

// ===== HP =====
const hpStat = document.getElementById('hpStat');
if (hpStat) {
    const newHpStat = hpStat.cloneNode(true);
    hpStat.parentNode.replaceChild(newHpStat, hpStat);
    newHpStat.addEventListener('click', () => {
        hpValueInput.value = state.hp;
        hpMaxInput.value = state.maxHp;
        hpModal.style.display = 'flex';
    });
}

document.getElementById('hpSaveBtn')?.addEventListener('click', () => {
    const newHp = parseFloat(hpValueInput.value);
    const newMax = parseFloat(hpMaxInput.value);
    if (!isNaN(newHp)) state.hp = newHp;
    if (!isNaN(newMax)) state.maxHp = newMax;
    updateUI();
    saveToServer();
    hpModal.style.display = 'none';
});
document.getElementById('hpCloseBtn')?.addEventListener('click', () => hpModal.style.display = 'none');

// ===== БЫСТРЫЕ КНОПКИ ДЕНЕГ =====
document.querySelectorAll('.quick-coin-btn').forEach(btn => {
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    newBtn.addEventListener('click', () => {
        const amount = parseInt(newBtn.dataset.amount);
        const current = parseFloat(coinInput.value) || 0;
        coinInput.value = current + amount;
    });
});

// ===== АВАТАРКИ =====
const uploadAvatarBtn = document.getElementById('uploadAvatarBtn');
const avatarInput = document.getElementById('avatarInput');
const avatarForm = document.getElementById('avatarForm');

if (uploadAvatarBtn) {
    uploadAvatarBtn.onclick = function(e) {
        e.preventDefault();
        console.log('Клик по кнопке загрузки');
        avatarInput.click();
    };
}

if (avatarInput) {
    avatarInput.onchange = function() {
        if (avatarInput.files.length > 0) {
            console.log('Файл выбран:', avatarInput.files[0].name);
            console.log('Отправка формы...');
            avatarForm.submit();
        }
    };
}
if (deleteAvatarBtn) {
    const newDelete = deleteAvatarBtn.cloneNode(true);
    deleteAvatarBtn.parentNode.replaceChild(newDelete, deleteAvatarBtn);
    newDelete.addEventListener('click', () => {
        if (confirm('Удалить аватар?')) deleteAvatarForm.submit();
    });
}
// ===== ЗАКРЫТИЕ МОДАЛОК ПО КЛИКУ ВНЕ =====
document.addEventListener('click', (e) => {
    if (!e.target.closest('.dice-container')) {
        const menu = document.getElementById('diceMenu');
        if (menu) menu.style.display = 'none';
    }
    if (!e.target.closest('.stat-modal') && !e.target.closest('#coinStat') && !e.target.closest('#hpStat')) {
        if (coinModal) coinModal.style.display = 'none';
        if (hpModal) hpModal.style.display = 'none';
    }
});

// ===== ЗАПУСК =====
updateXPDisplay();
renderModules();
toggleAvatarButtons();
updateUI(); // добавили принудительное обновление при загрузке

// Делаем функции глобальными
window.updateXPDisplay = updateXPDisplay;
window.updateUI = updateUI;
window.state = state;