const API_BASE = 'http://localhost:3000/api';

async function apiRequest(url, method = 'GET', data = null, headers = {}) {
  const options = {
    method,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...headers
    }
  };
  if (data) {
    if (method === 'GET') {
      url += '?' + new URLSearchParams(data).toString();
    } else {
      options.body = JSON.stringify(data);
    }
  }
  const response = await fetch(url, options);
  if (!response.ok) {
    const errorText = await response.text();
    throw new Error(`Errore HTTP ${response.status}: ${errorText}`);
  }
  const contentType = response.headers.get('content-type') || '';
  return contentType.includes('application/json') ? response.json() : response.status;
}

// DOM Elements
const notaForm = document.querySelector('#nota-form');
const notaInput = document.querySelector('#insert-nota');
const divLista = document.querySelector('#note-list-div');
const listaForm = document.querySelector('#lista-form');
const listaInput = document.querySelector('#insert-lista');
const mainListTree = document.querySelector('#main-list-tree');
const toggleArchiveBtn = document.querySelector('#toggle-archive-btn');
const tagForm = document.querySelector('#tag-form');
const tagInput = document.querySelector('#insert-tag');
const availableTagsCheckboxes = document.querySelector('#available-tags-checkboxes');
const saveListTagsBtn = document.querySelector('#save-list-tags-btn');
const selectedListNameSpan = document.querySelector('#selected-list-name');

let listaAttivaId = null;
let showingArchived = false;
let allTags = [];

// Funzione di sicurezza per controllare se una lista è selezionata
function ensureListaAttiva() {
  if (!listaAttivaId) {
    alert('Seleziona una lista prima!');
    return false;
  }
  return true;
}

// --- TAGS ---
async function fetchAllTags() {
  const tagsRes = await apiRequest(`${API_BASE}/tags`);
  allTags = tagsRes.data ?? tagsRes;
  renderTagListUI();
}

function renderTagListUI() {
  availableTagsCheckboxes.innerHTML = '';
  allTags.forEach(tag => {
    const wrapper = document.createElement('div');

    const cb = document.createElement('input');
    cb.type = 'checkbox';
    cb.id = `tag-${tag.id}`;
    cb.value = tag.id;

    const lbl = document.createElement('label');
    lbl.htmlFor = cb.id;
    lbl.textContent = tag.nome || tag.name;

    const del = document.createElement('button');
    del.textContent = 'X';
    del.title = 'Elimina tag';
    del.onclick = async () => {
      if (confirm(`Eliminare il tag "${lbl.textContent}"?`)) {
        await apiRequest(`${API_BASE}/tags/${tag.id}`, 'DELETE');
        await fetchAllTags();
        if (listaAttivaId) await populateTagsForList(listaAttivaId);
      }
    };

    wrapper.append(cb, lbl, del);
    availableTagsCheckboxes.appendChild(wrapper);
  });
}

async function createTag(nome) {
  await apiRequest(`${API_BASE}/tags`, 'POST', { nome });
  await fetchAllTags();
}

async function populateTagsForList(listaId) {
  if (!listaId) {
    availableTagsCheckboxes.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    return;
  }
  const res = await apiRequest(`${API_BASE}/listas/${listaId}/tags`);
  const tagsForList = res.data ?? res;
  const ids = tagsForList.map(t => t.id);
  allTags.forEach(tag => {
    const cb = document.querySelector(`#tag-${tag.id}`);
    if (cb) cb.checked = ids.includes(tag.id);
  });
}

async function saveTagsForList(listaId, tagIds) {
  await apiRequest(`${API_BASE}/listas/${listaId}/tags/sync`, 'POST', { tagIds });
}

// --- LISTE ---
async function fetchLists(includeArchived = false) {
  const params = includeArchived ? { include_archived: true } : {};
  const res = await apiRequest(`${API_BASE}/listas`, 'GET', params);
  return res.data ?? res;
}

async function toggleArchiveList(listaId, archivia) {
  const action = archivia ? 'archive' : 'unarchive';
  await apiRequest(`${API_BASE}/listas/${listaId}/${action}`, 'PUT');
}

async function aggiornaListeGerarchiche() {
  const listas = await fetchLists(showingArchived);
  function build(lists, parentId = null) {
    const filtered = lists.filter(l => l.parent_lista_id === parentId);
    if (!filtered.length) return null;
    const ul = document.createElement('ul');
    filtered.forEach(l => {
      const li = document.createElement('li');
      li.dataset.listId = l.id;

      const span = document.createElement('span');
      span.className = 'list-name';
      span.textContent = l.nome || l.name;
      span.style.cursor = 'pointer';
      span.onclick = () => {
        listaAttivaId = l.id;
        aggiornaListaNote(l.id);
        populateTagsForList(l.id);
        document.querySelectorAll('.list-name').forEach(el => el.classList.remove('active'));
        span.classList.add('active');
        selectedListNameSpan.textContent = span.textContent;
      };
      li.append(span);

      // Bottone modifica nome lista
      const EditButton = document.createElement('button');
      EditButton.textContent = 'Modifica';
      EditButton.onclick = async e => {
        e.stopPropagation();
        const newName = prompt('Nuovo nome lista:', l.nome || l.name);
        if (newName) {
          await apiRequest(`${API_BASE}/listas/${l.id}`, 'PUT', { nome: newName });
          await aggiornaListeGerarchiche();
        }
      };
      li.append(EditButton);

      const btn = document.createElement('button');
      btn.textContent = (l.archiviata || l.archived) ? 'Disarchivia' : 'Archivia';
      btn.onclick = async e => {
        e.stopPropagation();
        await toggleArchiveList(l.id, !(l.archiviata || l.archived));
        if (listaAttivaId === l.id) {
          listaAttivaId = null;
          aggiornaListaNote(null);
          populateTagsForList(null);
          selectedListNameSpan.textContent = 'Nessuna';
        }
        await aggiornaListeGerarchiche();
      };
      li.append(btn);

      const childUl = build(lists, l.id);
      if (childUl) li.append(childUl);
      ul.append(li);
    });
    return ul;
  }

  mainListTree.innerHTML = '';
  const root = build(listas);
  if (root) mainListTree.appendChild(root);
  else mainListTree.innerHTML = '<p>Nessuna lista disponibile.</p>';
}

// --- NOTE ---
async function aggiornaListaNote(listaId) {
  if (!listaId) {
    divLista.innerHTML = '<p>Seleziona una lista per visualizzare le note.</p>';
    return;
  }
  const res = await apiRequest(`${API_BASE}/listas/${listaId}/notes`);
  const notes = res.data ?? res;
  divLista.innerHTML = '';
  if (!notes.length) {
    divLista.innerHTML = '<p>Nessuna nota per questa lista.</p>';
    return;
  }
  const ul = document.createElement('ul');

  notes.forEach(n => {
    const li = document.createElement('li');
    li.className = 'note-item';

    // Checkbox
    const cb = document.createElement('input');
    cb.type = 'checkbox';
    cb.checked = n.checkbox || false;

    cb.onchange = async () => {
      try {
        await apiRequest(`${API_BASE}/notes/update/${n.id}`, 'PUT', { checkbox: cb.checked });
        await aggiornaListaNote(listaId);
      } catch (error) {
        console.error('Errore aggiornamento checkbox:', error);
      }
    };

    // Testo nota con linea attraversata se completata
    const span = document.createElement('span');
    span.textContent = n.text;
    if (n.checkbox) span.style.textDecoration = 'line-through';

    // Bottone elimina
    const del = document.createElement('button');
    del.textContent = 'X';
    del.onclick = () => {
      if (confirm('Eliminare questa nota?')) {
        apiRequest(`${API_BASE}/notes/del/${n.id}`, 'DELETE')
          .then(() => aggiornaListaNote(listaId));
      }
    };

    // Bottone modifica
    const editBtn = document.createElement('button');
    editBtn.textContent = 'Modifica';
    editBtn.onclick = async () => {
      const newText = prompt('Nuovo testo nota:', n.text || '');
      if (newText) {
        try {
          await apiRequest(`${API_BASE}/notes/update/${n.id}`, 'PUT', { text: newText });
          await aggiornaListaNote(listaId);
        } catch (error) {
          console.error('Errore aggiornamento nota:', error);
        }
      }
    };

    li.append(cb, span, del, editBtn);
    ul.appendChild(li);
  });

  divLista.appendChild(ul);
}

// --- INIT ---
document.addEventListener('DOMContentLoaded', async () => {
  await fetchAllTags();
  await aggiornaListeGerarchiche();

  listaForm.onsubmit = async e => {
    e.preventDefault();
    const nome = listaInput.value.trim();
    if (!nome) return alert('Inserisci nome lista!');
    await apiRequest(`${API_BASE}/listas`, 'POST', {
      nome,
      parent_lista_id: null  // Nessun parent
    });
    listaInput.value = '';
    await aggiornaListeGerarchiche();
  };

  toggleArchiveBtn.onclick = async () => {
    showingArchived = !showingArchived;
    toggleArchiveBtn.textContent = showingArchived ? 'Nascondi Archivio' : 'Mostra Archivio';
    await aggiornaListeGerarchiche();
  };

  notaForm.onsubmit = async e => {
    e.preventDefault();
    if (!ensureListaAttiva()) return;
    const text = notaInput.value.trim();
    if (!text) return;
    await apiRequest(`${API_BASE}/listas/${listaAttivaId}/notes`, 'POST', {
      text,
      completed: false
    });
    notaInput.value = '';
    await aggiornaListaNote(listaAttivaId);
  };

  tagForm.onsubmit = async e => {
    e.preventDefault();
    const nome = tagInput.value.trim();
    if (!nome) return alert('Inserisci nome tag!');
    await createTag(nome);
    tagInput.value = '';
  };

  saveListTagsBtn.onclick = async () => {
    if (!ensureListaAttiva()) return;
    const checked = Array.from(availableTagsCheckboxes.querySelectorAll('input:checked'))
      .map(cb => parseInt(cb.value));
    await saveTagsForList(listaAttivaId, checked);
    alert('Tag salvati!');
  };
});
