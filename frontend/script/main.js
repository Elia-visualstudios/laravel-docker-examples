const API_BASE = 'http://172.20.0.2:3000/api'; // Indirizzo IP corretto della tua VirtualBox per le API

async function apiRequest(url, method = 'GET', data = null, headers = {}) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...headers
        }
    };

    if (method !== 'GET' && data) {
        options.body = JSON.stringify(data);
    }

    if (method === 'GET' && data) {
        const params = new URLSearchParams(data).toString();
        url += '?' + params;
    }

    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Errore HTTP ${response.status}: ${errorText}`);
        }
        if (response.headers.get('content-type')?.includes('application/json')) {
            return await response.json();
        }
        return response.status;
    } catch (error) {
        console.error('Errore nella richiesta API:', error);
        throw error;
    }
}

const notaForm = document.querySelector('#nota-form');
const notaInput = document.querySelector('#insert-nota');
const divLista = document.querySelector('#note-list-div');
const listaForm = document.querySelector('#lista-form');
const listaInput = document.querySelector('#insert-lista');
const mainListTree = document.querySelector('#main-list-tree');
// NUOVA COSTANTE: Seleziona il menu a tendina delle liste genitore
const parentListaSelect = document.querySelector('#parent-lista-select'); 


let listaAttivaId = null;

async function aggiornaNotaStato(id, checkboxChecked) {
    try {
        await apiRequest(`${API_BASE}/notes/update/${id}`, 'PUT', { checkbox: checkboxChecked });
        aggiornaListaNote(listaAttivaId);
    } catch (e) {
        console.error('Note update error:', e);
    }
}

async function cancellaNota(id) {
    if (!confirm('Eliminare nota?')) return;
    try {
        await apiRequest(`${API_BASE}/notes/del/${id}`, 'DELETE');
        aggiornaListaNote(listaAttivaId);
    } catch (e) {
        console.error('Note delete error:', e);
    }
}

async function cancellaLista(id) {
    if (!confirm('Eliminare lista e figli?')) return;
    try {
        await apiRequest(`${API_BASE}/lists/${id}`, 'DELETE');
        aggiornaListeGerarchiche();
        // AGGIORNAMENTO: Dopo aver cancellato una lista, ripopola anche il selettore
        populateParentListSelect(); 
        if (listaAttivaId == id) {
            listaAttivaId = null;
            aggiornaListaNote(null);
        }
    } catch (e) {
        console.error('List delete error:', e);
    }
}

function renderNestedLists(lists, parentUl) {
    lists.forEach(list => {
        const li = document.createElement('li');
        li.className = 'list-item';
        li.dataset.listId = list.id;

        const listNameSpan = document.createElement('span');
        listNameSpan.textContent = list.nome;
        listNameSpan.className = 'list-name';
        listNameSpan.addEventListener('click', () => {
            document.querySelectorAll('.list-name').forEach(el => el.classList.remove('active'));
            listNameSpan.classList.add('active');
            listaAttivaId = list.id;
            aggiornaListaNote(listaAttivaId);
        });
        li.appendChild(listNameSpan);

        if (list.children && list.children.length > 0) {
            const childUl = document.createElement('ul');
            childUl.className = 'nested-list';
            renderNestedLists(list.children, childUl);
            li.appendChild(childUl);
        }

        const deleteListButton = document.createElement('button');
        deleteListButton.textContent = 'X';
        deleteListButton.className = 'delete-button-list';
        deleteListButton.onclick = () => cancellaLista(list.id);
        li.appendChild(deleteListButton);
        parentUl.appendChild(li);
    });
}

// NUOVA FUNZIONE: Popola il menu a tendina delle liste per la selezione genitore
async function populateParentListSelect() {
    try {
        // Chiediamo le liste senza figli ricorsivi per semplicità e velocità
        const { data: lists } = await apiRequest(`${API_BASE}/lists`); 
        
        // Svuota le opzioni esistenti, ma mantieni l'opzione "Nessun Genitore"
        parentListaSelect.innerHTML = '<option value="">Nessun Genitore (Lista di primo livello)</option>';

        lists.forEach(list => {
            const option = document.createElement('option');
            option.value = list.id;
            option.textContent = list.nome;
            parentListaSelect.appendChild(option);
        });
    } catch (e) {
        console.error('Errore nel caricare le liste per il selettore:', e);
    }
}


async function aggiornaListeGerarchiche() {
    try {
        // La richiesta al backend ora recupera le liste radice con i figli già caricati ricorsivamente
        const { data: lists } = await apiRequest(`${API_BASE}/lists?include_children=true`);
        mainListTree.innerHTML = '';
        
        // Renderizza direttamente le liste ricevute, che sono già le root con i loro figli
        renderNestedLists(lists, mainListTree); 

        if (lists.length > 0) {
            const firstSpan = mainListTree.querySelector('.list-name');
            if (firstSpan) {
                firstSpan.classList.add('active');
                listaAttivaId = parseInt(firstSpan.closest('.list-item').dataset.listId);
                aggiornaListaNote(listaAttivaId);
            }
        } else {
            listaAttivaId = null;
            aggiornaListaNote(null);
        }
    } catch (e) {
        console.error('List load error:', e);
        mainListTree.innerHTML = '<p>Errore liste.</p>';
    }
}

notaForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const nota = notaInput.value.trim();
    if (nota === '' || !listaAttivaId) {
        alert('Inserisci nota e seleziona lista!');
        return;
    }
    try {
        await apiRequest(`${API_BASE}/lists/${listaAttivaId}/notes`, 'POST', { text: nota, checkbox: false });
        notaInput.value = '';
        aggiornaListaNote(listaAttivaId);
    } catch (e) {
        console.error('Add note error:', e);
    }
});

async function aggiornaListaNote(listaId) {
    if (!listaId) {
        divLista.innerHTML = '<p>Seleziona una lista.</p>';
        return;
    }
    try {
        const { data: notes } = await apiRequest(`${API_BASE}/lists/${listaId}/notes`);
        divLista.innerHTML = '';
        if (notes.length === 0) {
            divLista.innerHTML = '<p>Nessuna nota.</p>';
            return;
        }
        const ul = document.createElement('ul');
        notes.forEach(note => {
            const li = document.createElement('li');
            li.className = 'note-item';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = note.checkbox;
            checkbox.addEventListener('change', () => aggiornaNotaStato(note.id, checkbox.checked));

            const textSpan = document.createElement('span');
            textSpan.textContent = note.text;
            if (note.checkbox) {
                textSpan.style.textDecoration = 'line-through';
            }

            const deleteButton = document.createElement('button');
            deleteButton.textContent = 'X';
            deleteButton.className = 'delete-button';
            deleteButton.onclick = () => cancellaNota(note.id);

            li.append(checkbox, textSpan, deleteButton);
            ul.appendChild(li);
        });
        divLista.appendChild(ul);
    } catch (e) {
        console.error('Note load error:', e);
        divLista.innerHTML = '<p>Errore note.</p>';
    }
}

listaForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const nome = listaInput.value.trim();
    // Ottieni il parent_lista_id dal selettore
    const parentId = parentListaSelect.value === '' ? null : parseInt(parentListaSelect.value); 
    
    if (nome === '') {
        alert('Inserisci un nome per la lista!');
        return;
    }
    
    try {
        // Invia anche parent_lista_id nella richiesta POST
        await apiRequest(`${API_BASE}/lists`, 'POST', { nome, parent_lista_id: parentId }); 
        listaInput.value = '';
        
        // Dopo aver creato una lista, aggiorna sia l'albero gerarchico che il selettore del genitore
        aggiornaListeGerarchiche(); 
        populateParentListSelect(); 
    } catch (e) {
        console.error('Add list error:', e);
        alert('Errore durante l\'aggiunta della lista.');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    aggiornaListeGerarchiche();
    // Chiamiamo questa funzione all'avvio per popolare il menu a tendina
    populateParentListSelect(); 

    // QUESTE RIGHE SONO IL LISTENER 'change' PER parentListaSelect
    parentListaSelect.addEventListener('change', (event) => {
        const selectedListId = event.target.value; // Corretto: nome variabile uniforme
        if (selectedListId) { 
            listaAttivaId = parseInt(selectedListId); // Ora usa il nome corretto
            aggiornaListaNote(listaAttivaId);

            // Opzionale: Rimuovi la selezione attiva da altri elementi della lista
            // e seleziona l'elemento corrispondente nel mainListTree se esiste
            document.querySelectorAll('.list-name').forEach(el => el.classList.remove('active'));
            const listItemInTree = mainListTree.querySelector(`.list-item[data-list-id="${listaAttivaId}"] .list-name`);
            if (listItemInTree) {
                listItemInTree.classList.add('active');
            }
        } else {
            // Selezionata l'opzione "Nessun Genitore", resetta la lista attiva
            listaAttivaId = null;
            aggiornaListaNote(null);
            document.querySelectorAll('.list-name').forEach(el => el.classList.remove('active'));
        }
    });
});