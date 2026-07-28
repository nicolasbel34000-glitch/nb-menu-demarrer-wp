/**
 * WP Start Menu admin interactions.
 * Copyright (C) 2026 NBForm. Original author: NBForm.
 * SPDX-License-Identifier: GPL-2.0-or-later
 */
(function () {
  'use strict';

  const configNode = document.getElementById('wpsm-config');
  const root = document.getElementById('wpsm-root');
  if (!configNode || !root) return;

  let config;
  try {
    config = JSON.parse(configNode.textContent);
  } catch (error) {
    return;
  }

  const apps = Array.isArray(config.apps) ? config.apps : [];
  const shortcuts = config.layout && Array.isArray(config.layout.shortcuts)
    ? config.layout.shortcuts.map(shortcutFromRaw).filter(Boolean)
    : [];
  const appMap = new Map([...apps, ...shortcuts].map((app) => [app.id, app]));
  const state = {
    layout: normalizeLayout(config.layout),
    activeView: 'favorites',
    organize: false,
    selectedFolder: null,
    openSubmenu: null,
    dragged: null,
  };

  const trigger = root.querySelector('#wpsm-trigger');
  const panel = root.querySelector('#wpsm-panel');
  const closeButton = root.querySelector('.wpsm-close');
  const organizeButton = root.querySelector('.wpsm-organize');
  const searchInput = root.querySelector('#wpsm-search');
  const status = root.querySelector('.wpsm-status');
  const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
  const views = {
    favorites: root.querySelector('#wpsm-view-favorites'),
    folders: root.querySelector('#wpsm-view-folders'),
    alpha: root.querySelector('#wpsm-view-alpha'),
    search: root.querySelector('#wpsm-view-search'),
  };

  trigger.addEventListener('click', togglePanel);
  closeButton.addEventListener('click', () => closePanel());
  organizeButton.addEventListener('click', toggleOrganize);
  root.querySelector('.wpsm-all-apps').addEventListener('click', () => setView('alpha'));
  searchInput.addEventListener('input', renderSearch);
  searchInput.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && searchInput.value) {
      searchInput.value = '';
      renderSearch();
      event.stopPropagation();
    }
  });

  tabs.forEach((tab) => tab.addEventListener('click', () => setView(tab.dataset.view)));

  document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      if (!isOpen()) openPanel();
      searchInput.focus();
      searchInput.select();
      event.preventDefault();
    } else if (event.key === 'Escape' && isOpen()) {
      closePanel();
    }
  });

  document.addEventListener('click', (event) => {
    const eventPath = typeof event.composedPath === 'function' ? event.composedPath() : [];
    const clickedInsidePanel = eventPath.includes(panel) || panel.contains(event.target);
    const clickedTrigger = eventPath.includes(trigger) || trigger.contains(event.target);
    if (isOpen() && !clickedInsidePanel && !clickedTrigger) {
      closePanel(false);
      return;
    }
    if (state.openSubmenu && !event.target.closest('.wpsm-app')) {
      state.openSubmenu = null;
      renderActiveView();
    }
  });

  window.addEventListener('pagehide', flushPendingLayout);

  mountTrigger();
  renderAll();

  function mountTrigger() {
    const adminMenu = document.getElementById('adminmenu');
    if (!adminMenu) {
      trigger.classList.add('is-floating');
      return;
    }

    const menuItem = document.createElement('li');
    menuItem.id = 'wpsm-adminmenu-item';
    menuItem.className = 'menu-top wpsm-adminmenu-item';
    menuItem.appendChild(trigger);
    adminMenu.appendChild(menuItem);
    document.body.classList.add('wpsm-has-trigger');
  }

  function normalizeLayout(layout) {
    const safe = layout && typeof layout === 'object' ? layout : {};
    return {
      favorites: Array.isArray(safe.favorites)
        ? safe.favorites.filter((id, index, list) => appMap.has(id) && list.indexOf(id) === index)
        : [],
      folders: Array.isArray(safe.folders)
        ? safe.folders.map((folder) => ({
            id: String(folder.id || uniqueId()),
            name: String(folder.name || '').slice(0, 40),
            items: Array.isArray(folder.items)
              ? folder.items.filter((id, index, list) => appMap.has(id) && list.indexOf(id) === index)
              : [],
          })).filter((folder) => folder.name)
        : [],
      shortcuts: shortcuts.map((shortcut) => ({
        id: shortcut.id,
        title: shortcut.title,
        url: shortcut.url,
      })),
    };
  }

  function isOpen() {
    return trigger.getAttribute('aria-expanded') === 'true';
  }

  function openPanel() {
    panel.hidden = false;
    trigger.setAttribute('aria-expanded', 'true');
    document.body.classList.add('wpsm-is-open');
    window.requestAnimationFrame(() => {
      panel.classList.add('is-visible');
      searchInput.focus();
    });
  }

  function closePanel(restoreFocus = true) {
    panel.classList.remove('is-visible');
    trigger.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('wpsm-is-open');
    window.setTimeout(() => {
      if (!isOpen()) {
        panel.hidden = true;
      }
    }, 160);
    if (restoreFocus) trigger.focus();
  }

  function togglePanel() {
    if (isOpen()) closePanel();
    else openPanel();
  }

  function toggleOrganize() {
    state.organize = !state.organize;
    organizeButton.setAttribute('aria-pressed', String(state.organize));
    organizeButton.classList.toggle('is-active', state.organize);
    organizeButton.querySelector('span:last-child').textContent = state.organize ? config.i18n.done : config.i18n.organize;
    panel.classList.toggle('is-organizing', state.organize);
    renderAll();
  }

  function setView(view) {
    if (!views[view]) return;
    state.activeView = view;
    searchInput.value = '';
    Object.entries(views).forEach(([name, node]) => {
      node.hidden = name !== view;
    });
    tabs.forEach((tab) => tab.setAttribute('aria-selected', String(tab.dataset.view === view)));
    renderActiveView();
  }

  function renderAll() {
    renderFavorites();
    renderFolders();
    renderAlpha();
  }

  function renderActiveView() {
    if (state.activeView === 'favorites') renderFavorites();
    if (state.activeView === 'folders') renderFolders();
    if (state.activeView === 'alpha') renderAlpha();
  }

  function renderFavorites() {
    const node = views.favorites;
    node.textContent = '';
    const heading = sectionHeading(config.i18n.favorites, config.i18n.favoritesDescription);
    const addShortcut = button(config.i18n.addUrl, 'wpsm-button wpsm-add-shortcut', 'dashicons-admin-links');
    addShortcut.addEventListener('click', createShortcut);
    heading.appendChild(addShortcut);
    node.appendChild(heading);

    const grid = el('div', 'wpsm-app-grid wpsm-favorites-grid');
    grid.dataset.dropzone = 'favorites';
    state.layout.favorites.forEach((id, index) => {
      const app = appMap.get(id);
      if (app) grid.appendChild(appCard(app, { rank: index + 1, context: 'favorites' }));
    });
    if (!grid.children.length) {
      grid.appendChild(emptyState(config.i18n.emptyFavorites));
    }
    bindDropzone(grid, (draggedId, beforeId) => {
      moveFavorite(draggedId, beforeId);
    });
    node.appendChild(grid);

    const customShortcuts = Array.from(appMap.values()).filter((item) => item.custom);
    if (state.organize && customShortcuts.length) {
      node.appendChild(sectionHeading(config.i18n.customShortcuts, config.i18n.customShortcutsHelp));
      const shortcutGrid = el('div', 'wpsm-app-grid wpsm-shortcut-library');
      customShortcuts.forEach((shortcut) => shortcutGrid.appendChild(appCard(shortcut, { context: 'shortcut-library', compact: true })));
      node.appendChild(shortcutGrid);
    }

    const folders = el('div', 'wpsm-favorite-folders');
    folders.appendChild(sectionHeading(config.i18n.folders, config.i18n.folderDropHelp));
    const folderGrid = el('div', 'wpsm-folder-strip');
    state.layout.folders.forEach((folder) => folderGrid.appendChild(folderTile(folder, true)));
    folders.appendChild(folderGrid);
    node.appendChild(folders);
  }

  function renderFolders() {
    const node = views.folders;
    node.textContent = '';

    const heading = sectionHeading(config.i18n.folders, config.i18n.foldersDescription);
    if (state.organize) {
      const add = button(config.i18n.newFolderButton, 'wpsm-button wpsm-add-folder', 'dashicons-plus-alt2');
      add.addEventListener('click', createFolder);
      heading.appendChild(add);
    }
    node.appendChild(heading);

    const folderGrid = el('div', 'wpsm-folder-grid');
    state.layout.folders.forEach((folder) => folderGrid.appendChild(folderTile(folder, false)));
    if (!state.layout.folders.length) folderGrid.appendChild(emptyState(config.i18n.emptyFolders));
    node.appendChild(folderGrid);

    const selected = state.layout.folders.find((folder) => folder.id === state.selectedFolder);
    if (selected) node.appendChild(folderDetail(selected));

    if (state.organize) {
      node.appendChild(sectionHeading(config.i18n.availableApps, config.i18n.availableAppsHelp));
      const palette = el('div', 'wpsm-app-grid wpsm-palette');
      Array.from(appMap.values()).forEach((app) => palette.appendChild(appCard(app, { context: 'palette', compact: true })));
      node.appendChild(palette);
    }
  }

  function renderAlpha() {
    const node = views.alpha;
    node.textContent = '';
    const info = el('p', 'wpsm-alpha-note');
    info.appendChild(icon('dashicons-info-outline'));
    info.append(` ${config.i18n.alphaNote}`);
    node.appendChild(info);

    const groups = new Map();
    apps.forEach((app) => {
      const letter = firstLetter(app.title);
      if (!groups.has(letter)) groups.set(letter, []);
      groups.get(letter).push(app);
    });

    const list = el('div', 'wpsm-alpha-list');
    Array.from(groups.keys()).sort(localeCompare).forEach((letter) => {
      const group = el('section', 'wpsm-alpha-group');
      const heading = el('h3', 'wpsm-alpha-letter');
      heading.textContent = letter;
      group.appendChild(heading);
      const items = el('div', 'wpsm-alpha-items');
      groups.get(letter).forEach((app) => items.appendChild(alphaRow(app)));
      group.appendChild(items);
      list.appendChild(group);
    });
    node.appendChild(list);
  }

  function renderSearch() {
    const term = normalize(searchInput.value.trim());
    if (!term) {
      views.search.hidden = true;
      views[state.activeView].hidden = false;
      return;
    }

    Object.values(views).forEach((view) => { view.hidden = true; });
    views.search.hidden = false;
    views.search.textContent = '';

    const matches = [];
    appMap.forEach((app) => {
      if (normalize(app.title).includes(term)) matches.push({ type: 'app', app });
      (app.children || []).forEach((child) => {
        if (normalize(`${child.title} ${app.title}`).includes(term)) matches.push({ type: 'child', app, child });
      });
    });

    views.search.appendChild(sectionHeading(
      config.i18n.results,
      `${matches.length} ${matches.length === 1 ? config.i18n.result : config.i18n.resultsCount}`,
    ));
    const results = el('div', 'wpsm-search-results');
    matches.forEach((match) => {
      const link = el('a', 'wpsm-search-result');
      link.href = match.type === 'child' ? match.child.url : match.app.url;
      link.appendChild(appIcon(match.app));
      const text = el('span', 'wpsm-search-result-text');
      const strong = document.createElement('strong');
      strong.textContent = match.type === 'child' ? match.child.title : match.app.title;
      text.appendChild(strong);
      if (match.type === 'child') {
        const small = document.createElement('small');
        small.textContent = match.app.title;
        text.appendChild(small);
      }
      link.appendChild(text);
      link.appendChild(icon('dashicons-arrow-right-alt2'));
      results.appendChild(link);
    });
    if (!matches.length) results.appendChild(emptyState(config.i18n.emptySearch));
    views.search.appendChild(results);
  }

  function appCard(app, options) {
    const card = el('div', `wpsm-app ${options.compact ? 'is-compact' : ''}`);
    card.dataset.appId = app.id;
    card.draggable = state.organize;
    bindDrag(card, app.id);

    if (options.rank) {
      const rank = el('span', 'wpsm-rank');
      rank.textContent = String(options.rank);
      card.appendChild(rank);
    }
    if (state.organize) card.appendChild(icon('dashicons-move', 'wpsm-drag-handle'));

    const link = el('a', 'wpsm-app-link');
    link.href = app.url;
    link.appendChild(appIcon(app));
    const label = el('span', 'wpsm-app-label');
    label.textContent = app.title;
    link.appendChild(label);
    card.appendChild(link);

    const controls = el('div', 'wpsm-app-controls');
    if (app.children && app.children.length && options.context !== 'palette') {
      const submenu = iconButton('dashicons-arrow-down-alt2', config.i18n.openSubmenu);
      submenu.setAttribute('aria-expanded', String(state.openSubmenu === app.id));
      submenu.addEventListener('click', (event) => {
        event.stopPropagation();
        state.openSubmenu = state.openSubmenu === app.id ? null : app.id;
        renderActiveView();
      });
      controls.appendChild(submenu);
    }
    if (state.organize) {
      const isFavorite = state.layout.favorites.includes(app.id);
      const favorite = iconButton(isFavorite ? 'dashicons-star-filled' : 'dashicons-star-empty', isFavorite ? config.i18n.removeFavorite : config.i18n.addFavorite);
      favorite.addEventListener('click', () => toggleFavorite(app.id));
      controls.appendChild(favorite);
      if (app.custom) {
        const edit = iconButton('dashicons-edit', config.i18n.editShortcut);
        edit.addEventListener('click', () => editShortcut(app.id));
        controls.appendChild(edit);
        const deleteButton = iconButton('dashicons-trash', config.i18n.deleteShortcut);
        deleteButton.addEventListener('click', () => deleteShortcut(app.id));
        controls.appendChild(deleteButton);
      }
      if (options.folderId) {
        const remove = iconButton('dashicons-remove', config.i18n.removeFromFolder);
        remove.addEventListener('click', () => removeFromFolder(options.folderId, app.id));
        controls.appendChild(remove);
      }
    }
    card.appendChild(controls);

    if (state.openSubmenu === app.id && app.children && app.children.length) {
      card.appendChild(submenuPanel(app));
    }
    return card;
  }

  function alphaRow(app) {
    const row = el('div', 'wpsm-alpha-row');
    row.dataset.appId = app.id;
    row.draggable = state.organize;
    bindDrag(row, app.id);
    const link = el('a', 'wpsm-alpha-link');
    link.href = app.url;
    link.appendChild(appIcon(app));
    const label = document.createElement('span');
    label.textContent = app.title;
    link.appendChild(label);
    row.appendChild(link);
    if (state.organize) {
      const active = state.layout.favorites.includes(app.id);
      const favorite = iconButton(active ? 'dashicons-star-filled' : 'dashicons-star-empty', active ? config.i18n.removeFavorite : config.i18n.addFavorite);
      favorite.addEventListener('click', () => toggleFavorite(app.id));
      row.appendChild(favorite);
    } else {
      row.appendChild(icon('dashicons-arrow-right-alt2'));
    }
    return row;
  }

  function folderTile(folder, compact) {
    const tile = el('button', `wpsm-folder ${compact ? 'is-compact' : ''}`);
    tile.type = 'button';
    tile.dataset.folderId = folder.id;
    tile.appendChild(icon('dashicons-category'));
    const name = el('span', 'wpsm-folder-name');
    name.textContent = folder.name;
    tile.appendChild(name);
    const count = el('span', 'wpsm-folder-count');
    count.textContent = String(folder.items.length);
    tile.appendChild(count);
    tile.addEventListener('click', () => {
      state.selectedFolder = folder.id;
      if (compact) setView('folders');
      else renderFolders();
    });
    bindDropzone(tile, (appId) => addToFolder(folder.id, appId));
    return tile;
  }

  function folderDetail(folder) {
    const detail = el('section', 'wpsm-folder-detail');
    const heading = sectionHeading(
      folder.name,
      `${folder.items.length} ${folder.items.length === 1 ? config.i18n.application : config.i18n.applications}`,
    );
    if (state.organize) {
      const actions = el('div', 'wpsm-folder-actions');
      const rename = iconButton('dashicons-edit', config.i18n.renameFolder);
      rename.addEventListener('click', () => renameFolder(folder.id));
      const remove = iconButton('dashicons-trash', config.i18n.deleteFolder);
      remove.addEventListener('click', () => deleteFolder(folder.id));
      actions.append(rename, remove);
      heading.appendChild(actions);
    }
    detail.appendChild(heading);
    const grid = el('div', 'wpsm-app-grid wpsm-folder-apps');
    grid.dataset.folderId = folder.id;
    folder.items.forEach((id) => {
      const app = appMap.get(id);
      if (app) grid.appendChild(appCard(app, { context: 'folder', folderId: folder.id }));
    });
    if (!folder.items.length) grid.appendChild(emptyState(config.i18n.emptyFolder));
    bindDropzone(grid, (appId) => addToFolder(folder.id, appId));
    detail.appendChild(grid);
    return detail;
  }

  function submenuPanel(app) {
    const submenu = el('div', 'wpsm-submenu');
    const title = el('strong', 'wpsm-submenu-title');
    title.textContent = app.title;
    submenu.appendChild(title);
    app.children.forEach((child) => {
      const link = el('a', 'wpsm-submenu-link');
      link.href = child.url;
      link.textContent = child.title;
      link.appendChild(icon('dashicons-arrow-right-alt2'));
      submenu.appendChild(link);
    });
    return submenu;
  }

  function toggleFavorite(id) {
    const index = state.layout.favorites.indexOf(id);
    if (index >= 0) state.layout.favorites.splice(index, 1);
    else state.layout.favorites.push(id);
    saveLayout();
    renderAll();
  }

  function moveFavorite(id, beforeId) {
    if (id === beforeId) return;
    const favorites = state.layout.favorites;
    const current = favorites.indexOf(id);
    if (current >= 0) favorites.splice(current, 1);
    const target = beforeId ? favorites.indexOf(beforeId) : -1;
    if (target >= 0) favorites.splice(target, 0, id);
    else favorites.push(id);
    saveLayout();
    renderAll();
  }

  function addToFolder(folderId, appId) {
    const folder = state.layout.folders.find((item) => item.id === folderId);
    if (!folder || !appMap.has(appId)) return;
    if (!folder.items.includes(appId)) folder.items.push(appId);
    state.selectedFolder = folderId;
    saveLayout();
    renderAll();
  }

  function removeFromFolder(folderId, appId) {
    const folder = state.layout.folders.find((item) => item.id === folderId);
    if (!folder) return;
    folder.items = folder.items.filter((id) => id !== appId);
    saveLayout();
    renderAll();
  }

  function createFolder() {
    const name = window.prompt(config.i18n.newFolder);
    if (!name || !name.trim()) return;
    const folder = { id: uniqueId(), name: name.trim().slice(0, 40), items: [] };
    state.layout.folders.push(folder);
    state.selectedFolder = folder.id;
    saveLayout();
    renderFolders();
  }

  function renameFolder(folderId) {
    const folder = state.layout.folders.find((item) => item.id === folderId);
    if (!folder) return;
    const name = window.prompt(config.i18n.renameFolder, folder.name);
    if (!name || !name.trim()) return;
    folder.name = name.trim().slice(0, 40);
    saveLayout();
    renderFolders();
  }

  function deleteFolder(folderId) {
    if (!window.confirm(config.i18n.deleteFolder)) return;
    state.layout.folders = state.layout.folders.filter((folder) => folder.id !== folderId);
    state.selectedFolder = null;
    saveLayout();
    renderFolders();
  }

  function createShortcut() {
    const title = window.prompt(config.i18n.shortcutName);
    if (!title || !title.trim()) return;
    const inputUrl = window.prompt(config.i18n.shortcutUrl, 'https://');
    const url = normalizeShortcutUrl(inputUrl);
    if (!url) {
      window.alert(config.i18n.invalidUrl);
      return;
    }

    const shortcut = {
      id: uniqueShortcutId(),
      title: title.trim().slice(0, 60),
      url,
      icon: 'dashicons-admin-links',
      children: [],
      custom: true,
    };
    appMap.set(shortcut.id, shortcut);
    state.layout.shortcuts.push({ id: shortcut.id, title: shortcut.title, url: shortcut.url });
    state.layout.favorites.push(shortcut.id);
    saveLayout();
    renderAll();
  }

  function editShortcut(id) {
    const shortcut = appMap.get(id);
    if (!shortcut || !shortcut.custom) return;
    const title = window.prompt(config.i18n.shortcutName, shortcut.title);
    if (!title || !title.trim()) return;
    const inputUrl = window.prompt(config.i18n.shortcutUrl, shortcut.url);
    const url = normalizeShortcutUrl(inputUrl);
    if (!url) {
      window.alert(config.i18n.invalidUrl);
      return;
    }

    shortcut.title = title.trim().slice(0, 60);
    shortcut.url = url;
    const stored = state.layout.shortcuts.find((item) => item.id === id);
    if (stored) {
      stored.title = shortcut.title;
      stored.url = shortcut.url;
    }
    saveLayout();
    renderAll();
  }

  function deleteShortcut(id) {
    const shortcut = appMap.get(id);
    if (!shortcut || !shortcut.custom || !window.confirm(config.i18n.deleteShortcut)) return;
    appMap.delete(id);
    state.layout.shortcuts = state.layout.shortcuts.filter((item) => item.id !== id);
    state.layout.favorites = state.layout.favorites.filter((itemId) => itemId !== id);
    state.layout.folders.forEach((folder) => {
      folder.items = folder.items.filter((itemId) => itemId !== id);
    });
    saveLayout();
    renderAll();
  }

  let saveTimer;
  function saveLayout() {
    window.clearTimeout(saveTimer);
    setStatus(config.i18n.saving);
    saveTimer = window.setTimeout(persistLayout, 80);
  }

  function layoutRequestBody() {
    return new URLSearchParams({
      action: config.ajax.action,
      nonce: config.ajax.nonce,
      layout: JSON.stringify(state.layout),
    });
  }

  async function persistLayout() {
    saveTimer = null;
    try {
      const response = await fetch(config.ajax.url, {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: layoutRequestBody().toString(),
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error('save failed');
      setStatus(config.i18n.saved, true);
    } catch (error) {
      setStatus(config.i18n.saveError, true);
    }
  }

  function flushPendingLayout() {
    if (!saveTimer || !navigator.sendBeacon) return;
    window.clearTimeout(saveTimer);
    saveTimer = null;
    navigator.sendBeacon(config.ajax.url, layoutRequestBody());
  }

  function setStatus(message, clear) {
    status.textContent = message;
    if (clear) window.setTimeout(() => { status.textContent = ''; }, 2600);
  }

  function bindDrag(node, appId) {
    node.addEventListener('dragstart', (event) => {
      if (!state.organize) {
        event.preventDefault();
        return;
      }
      state.dragged = appId;
      event.dataTransfer.effectAllowed = 'move';
      event.dataTransfer.setData('text/plain', appId);
      node.classList.add('is-dragging');
    });
    node.addEventListener('dragend', () => {
      state.dragged = null;
      node.classList.remove('is-dragging');
      root.querySelectorAll('.is-drag-over').forEach((item) => item.classList.remove('is-drag-over'));
    });
  }

  function bindDropzone(node, callback) {
    node.addEventListener('dragover', (event) => {
      if (!state.organize) return;
      event.preventDefault();
      event.dataTransfer.dropEffect = 'move';
      node.classList.add('is-drag-over');
    });
    node.addEventListener('dragleave', (event) => {
      if (!node.contains(event.relatedTarget)) node.classList.remove('is-drag-over');
    });
    node.addEventListener('drop', (event) => {
      if (!state.organize) return;
      event.preventDefault();
      event.stopPropagation();
      node.classList.remove('is-drag-over');
      const id = event.dataTransfer.getData('text/plain') || state.dragged;
      const target = event.target.closest('[data-app-id]');
      callback(id, target ? target.dataset.appId : null);
    });
  }

  function sectionHeading(title, subtitle) {
    const wrapper = el('div', 'wpsm-section-heading');
    const text = el('div', 'wpsm-section-heading-text');
    const heading = document.createElement('h3');
    heading.textContent = title;
    const description = document.createElement('p');
    description.textContent = subtitle;
    text.append(heading, description);
    wrapper.appendChild(text);
    return wrapper;
  }

  function appIcon(app) {
    const wrapper = el('span', `wpsm-app-icon ${iconTone(app.id)}`);
    wrapper.appendChild(icon(app.icon || 'dashicons-admin-generic'));
    return wrapper;
  }

  function icon(name, className) {
    const span = el('span', `dashicons ${name}${className ? ` ${className}` : ''}`);
    span.setAttribute('aria-hidden', 'true');
    return span;
  }

  function iconButton(iconName, label) {
    const control = el('button', 'wpsm-mini-button');
    control.type = 'button';
    control.setAttribute('aria-label', label);
    control.title = label;
    control.appendChild(icon(iconName));
    return control;
  }

  function button(label, className, iconName) {
    const control = el('button', className);
    control.type = 'button';
    if (iconName) control.appendChild(icon(iconName));
    const text = document.createElement('span');
    text.textContent = label;
    control.appendChild(text);
    return control;
  }

  function emptyState(message) {
    const empty = el('p', 'wpsm-empty');
    empty.textContent = message;
    return empty;
  }

  function el(tag, className) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    return node;
  }

  function iconTone(id) {
    const tones = ['is-blue', 'is-green', 'is-violet', 'is-orange', 'is-teal', 'is-slate'];
    let hash = 0;
    for (let i = 0; i < id.length; i += 1) hash = ((hash << 5) - hash) + id.charCodeAt(i);
    return tones[Math.abs(hash) % tones.length];
  }

  function uniqueId() {
    return `folder_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 8)}`;
  }

  function uniqueShortcutId() {
    return `shortcut_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 8)}`;
  }

  function shortcutFromRaw(raw) {
    if (!raw || typeof raw !== 'object') return null;
    const id = String(raw.id || '');
    const title = String(raw.title || '').trim().slice(0, 60);
    const url = normalizeShortcutUrl(raw.url);
    if (!/^shortcut_[a-z0-9_]{6,64}$/.test(id) || !title || !url) return null;
    return {
      id,
      title,
      url,
      icon: 'dashicons-admin-links',
      children: [],
      custom: true,
    };
  }

  function normalizeShortcutUrl(value) {
    let raw = String(value || '').trim();
    if (!raw) return null;
    if (!/^[a-z][a-z0-9+.-]*:/i.test(raw) && !raw.startsWith('/')) raw = `https://${raw}`;
    try {
      const parsed = new URL(raw, window.location.origin);
      if (!['http:', 'https:', 'mailto:', 'tel:'].includes(parsed.protocol)) return null;
      return parsed.href;
    } catch (error) {
      return null;
    }
  }

  function normalize(value) {
    return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('fr');
  }

  function firstLetter(value) {
    const letter = normalize(value).charAt(0).toUpperCase();
    return /[A-Z]/.test(letter) ? letter : '#';
  }

  function localeCompare(a, b) {
    return a.localeCompare(b, 'fr', { sensitivity: 'base' });
  }
}());
