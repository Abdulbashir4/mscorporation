const dialog = document.querySelector('#detail-dialog');
const title = document.querySelector('#dialog-title');
const body = document.querySelector('#dialog-body');
const search = document.querySelector('#dashboard-search');
let dialogTrigger;

function showPanel(heading, source, note = '') {
    dialogTrigger = document.activeElement;
    title.textContent = heading;
    body.replaceChildren();
    if (source) {
        const copy = source.cloneNode(true);
        copy.removeAttribute('id');
        copy.querySelectorAll('[id]').forEach((node) => node.removeAttribute('id'));
        copy.querySelectorAll('[hidden]').forEach((node) => node.removeAttribute('hidden'));
        body.append(copy);
    }
    if (note) {
        const paragraph = document.createElement('p');
        paragraph.className = 'dialog-note';
        paragraph.textContent = note;
        body.append(paragraph);
    }
    dialog.showModal();
}

const panels = {
    stock: ['Stock Alert', '.stock-panel .table-scroll'],
    activities: ['Recent Activities', '.activities'],
    notifications: ['Notifications', '.activities'],
    purchases: ['Purchase Orders', '.orders-panel .table-scroll'],
    invoices: ['Sales Invoices', '.invoices-panel .table-scroll'],
    profile: ['Administrator', null, 'Admin · Administrator — MS Corporation'],
    settings: ['Settings', null, 'MS Corporation ERP · Version 1.0.0. Business settings will be available when the corresponding module is implemented.'],
    help: ['Help & Support', null, 'Use the search bar to find products, invoices, purchase orders and customers in this dashboard. Select View All to expand a section. The displayed figures are reference demo data.'],
};

document.addEventListener('click', (event) => {
    const panelButton = event.target.closest('[data-panel]');
    const moduleButton = event.target.closest('[data-module]');
    if (panelButton) {
        const key = panelButton.dataset.panel;
        if (key === 'calendar') {
            showPanel('Calendar');
            const calendar = document.createElement('input');
            calendar.type = 'date';
            calendar.value = '2025-04-15';
            calendar.className = 'dialog-calendar';
            calendar.setAttribute('aria-label', 'Select a date');
            body.append(calendar);
            return;
        }
        const [heading, selector, note] = panels[key];
        showPanel(heading, selector ? document.querySelector(selector) : null, note);
    }
    if (moduleButton) {
        const module = moduleButton.dataset.module;
        if (module === 'Inventory') { window.location.assign('/inventory'); return; }
        if (module === 'Purchase') { window.location.assign('/purchase'); return; }
        if (module === 'Reports') { window.location.assign('/reports'); return; }
        if (module === 'Accounting') { window.location.assign('/accounting'); return; }
        if (module === 'Sales') { window.location.assign('/sales'); return; }
        if (module === 'Customer / Supplier') { window.location.assign('/customers-suppliers'); return; }
        const selectors = {
            Inventory: '.stock-panel .table-scroll',
            Purchase: '.orders-panel .table-scroll',
            Sales: '.invoices-panel .table-scroll',
            'Customer / Supplier': '.invoices-panel .table-scroll',
            Accounting: '.finance-panel',
            Reports: '.overview',
        };
        showPanel(moduleButton.dataset.submodule || module, document.querySelector(selectors[module]), 'Reference preview data. Full management features for this module are not yet implemented.');
    }
});

document.querySelector('#close-dialog').addEventListener('click', () => dialog.close());
dialog.addEventListener('click', (event) => { if (event.target === dialog) { const bounds = dialog.getBoundingClientRect(); if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close(); } });
dialog.addEventListener('close', () => dialogTrigger?.focus());

search.addEventListener('input', () => {
    if (document.querySelector('.reports-page')) { filterRecentReports(); return; }
    if (document.querySelector('.accounting-page')) { filterAccounting(); return; }
    if (document.querySelector('.contacts-page')) { document.querySelector('#contact-search').value = search.value; filterContacts(); return; }
    if (document.querySelector('.sales-page')) { filterSalesRecords(); return; }
    if (document.querySelector('.purchase-page')) { filterPurchaseTables(); return; }
    const query = search.value.trim().toLowerCase();
    let found = 0;
    document.querySelectorAll('main [data-searchable]').forEach((row) => {
        const matches = !query || row.dataset.searchable.toLowerCase().includes(query);
        row.hidden = !matches;
        if (matches) found++;
    });
    const message = document.querySelector('#search-message');
    if (!message) { document.querySelector('#product-search').value = search.value; filterProducts(); return; }
    message.hidden = !query;
    message.textContent = found ? `${found} matching records found.` : 'No matching products, invoices, orders or customers found.';
});

document.addEventListener('keydown', (event) => {
    if (event.key === '/' && !dialog.open && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
        event.preventDefault();
        search.focus();
    }
});

const menu = document.querySelector('.mobile-menu');
const sidebar = document.querySelector('#sidebar');
const backdrop = document.querySelector('.sidebar-backdrop');
function toggleMenu(open) {
    sidebar.classList.toggle('open', open);
    menu.setAttribute('aria-expanded', String(open));
    backdrop.hidden = !open;
}
menu.addEventListener('click', () => toggleMenu(!sidebar.classList.contains('open')));
backdrop.addEventListener('click', () => toggleMenu(false));
const sidebarMenuButtons = [...sidebar.querySelectorAll('[data-sidebar-menu]')];
function setSidebarMenu(button, open) {
    button.setAttribute('aria-expanded', String(open));
    document.getElementById(button.getAttribute('aria-controls')).hidden = !open;
}
sidebar.addEventListener('click', (event) => {
    const mainButton = event.target.closest('[data-sidebar-menu]');
    if (mainButton) {
        const menuUrl = mainButton.dataset.menuUrl;
        if (menuUrl && window.location.pathname !== new URL(menuUrl, window.location.origin).pathname) { window.location.assign(menuUrl); return; }
        const shouldOpen = mainButton.getAttribute('aria-expanded') !== 'true';
        sidebarMenuButtons.forEach((button) => setSidebarMenu(button, button === mainButton && shouldOpen));
        return;
    }
    if (event.target.closest('.nav-item')) {
        sidebarMenuButtons.forEach((button) => setSidebarMenu(button, false));
    }
    if (event.target.closest('button, a')) toggleMenu(false);
});
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') toggleMenu(false); });

// Prior-period demo values are derived from the percentage changes in the reference.
document.querySelector('#finance-period')?.addEventListener('change', (event) => {
    const previous = event.target.value === 'Last Month';
    document.querySelectorAll('main .finance-row').forEach((row) => {
        const amount = row.querySelector('strong');
        const change = row.querySelector('b');
        const current = Number(amount.dataset.current.replaceAll(',', ''));
        const rate = Number(change.textContent.replace(/[^\d]/g, '')) / 100;
        amount.textContent = `৳ ${new Intl.NumberFormat('en-IN').format(previous ? Math.round(current / (1 + rate)) : current)}`;
        change.hidden = previous;
    });
});


const productRows = [...document.querySelectorAll('[data-product]')];
const filterIds = ['category-filter', 'brand-filter', 'warehouse-filter', 'status-filter'];
function filterProducts() {
    const field = document.querySelector('#product-search');
    if (!field) return;
    const query = field.value.trim().toLowerCase();
    let matches = 0;
    productRows.forEach((row) => {
        const data = row.dataset;
        const searchable = [data.name, data.code, data.brand, data.model, data.description].join(' ').toLowerCase();
        const filters = [data.category, data.brand, data.warehouse, data.status];
        const visible = (!query || searchable.includes(query)) && filterIds.every((id, index) => !document.getElementById(id).value || document.getElementById(id).value === filters[index]);
        row.hidden = !visible;
        if (visible) matches++;
    });
    document.querySelector('#product-result-count').textContent = matches ? `Showing 1 to ${matches} of ${productRows.length} preview products` : `0 of ${productRows.length} preview products`;
    document.querySelector('#product-empty').hidden = matches > 0;
}
document.querySelector('#product-search')?.addEventListener('input', filterProducts);
filterIds.forEach((id) => document.getElementById(id)?.addEventListener('change', filterProducts));

function exportProducts() {
    const columns = ['Product', 'Code', 'Brand', 'Model', 'Category', 'Stock Qty', 'Unit Price (BDT)', 'Status', 'Warehouse'];
    const records = productRows.filter((row) => !row.hidden).map(({dataset: p}) => [p.name, p.code, p.brand, p.model, p.category, p.quantity, p.price, p.status, p.warehouse]);
    const csv = [columns, ...records].map((record) => record.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\r\n');
    const url = URL.createObjectURL(new Blob(['\uFEFF', csv], {type: 'text/csv;charset=utf-8;'}));
    const link = document.createElement('a');
    link.href = url;
    link.download = 'ms-corporation-inventory.csv';
    link.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function productDetails(row) {
    showPanel(row.dataset.name);
    const grid = document.createElement('dl');
    grid.className = 'product-detail-grid';
    for (const [label, key] of [['Product Code','code'], ['Brand','brand'], ['Model','model'], ['Category','category'], ['Stock Quantity','quantity'], ['Unit Price (BDT)','price'], ['Status','status'], ['Warehouse','warehouse']]) {
        const item = document.createElement('div');
        const term = document.createElement('dt');
        term.textContent = label;
        const value = document.createElement('dd');
        value.textContent = row.dataset[key];
        item.append(term, value);
        grid.append(item);
    }
    body.append(grid);
}

function productDraft(row = null) {
    showPanel(row ? 'Edit Product Draft' : 'Add New Product');
    const form = document.createElement('form');
    form.className = 'inventory-draft-form';
    const fields = [['Product Name','name'], ['Product Code','code'], ['Brand','brand'], ['Model','model'], ['Category','category'], ['Stock Quantity','quantity'], ['Unit Price (BDT)','price'], ['Warehouse','warehouse']];
    const draftKey = `ms-inventory-draft-${row?.dataset.code || 'new'}`;
    let saved = {};
    try { saved = JSON.parse(localStorage.getItem(draftKey) || '{}'); } catch { /* Browser storage may be unavailable. */ }
    for (const [labelText, key] of fields) {
        const label = document.createElement('label');
        label.textContent = labelText;
        const input = document.createElement('input');
        input.name = key;
        input.required = true;
        input.type = ['price','quantity'].includes(key) ? 'number' : 'text';
        if (input.type === 'number') { input.min = '0'; input.step = key === 'price' ? '0.01' : '1'; }
        input.value = saved[key] ?? row?.dataset[key] ?? '';
        label.append(input);
        form.append(label);
    }
    const note = document.createElement('p');
    note.className = 'draft-note';
    note.textContent = 'Preview mode: drafts are saved in this browser only. Live product creation and updates require a connected MySQL database.';
    const status = document.createElement('span');
    status.className = 'inventory-draft-status';
    status.setAttribute('role', 'status');
    const save = document.createElement('button');
    save.type = 'submit';
    save.className = 'primary-button';
    save.textContent = 'Save Draft';
    form.append(note, status, save);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        try {
            localStorage.setItem(draftKey, JSON.stringify(Object.fromEntries(new FormData(form))));
            status.textContent = 'Draft saved in this browser. Live inventory is unchanged.';
        } catch { status.textContent = 'Browser storage is unavailable. Your draft could not be saved.'; }
    });
    body.append(form);
}

document.addEventListener('click', (event) => {
    const details = event.target.closest('[data-product-details]');
    const edit = event.target.closest('[data-product-edit]');
    if (details) productDetails(productRows.find((row) => row.dataset.code === details.dataset.productDetails));
    if (edit) productDraft(productRows.find((row) => row.dataset.code === edit.dataset.productEdit));
    const action = event.target.closest('[data-inventory-action]')?.dataset.inventoryAction;
    if (!action) return;
    if (action === 'Export Products') { exportProducts(); return; }
    if (action === 'Add New Product') { productDraft(); return; }
    if (action === 'Low Stock Alert') { showPanel(action, document.querySelector('.inventory-alerts table')); return; }
    if (action === 'Warehouse') {
        showPanel('Warehouses', null, 'Main Warehouse · Secondary Warehouse — reference preview locations. Live warehouse management requires a connected MySQL database.');
        return;
    }
    showPanel(action, null, `${action} is a preview navigation entry. This page currently provides product browsing, filters, draft forms and CSV export. Stock transactions and imports will be available when the management module is connected to MySQL.`);
});



let purchaseDateRange = null;
function filterPurchaseTables() {
    const query = search.value.trim().toLowerCase();
    let total = 0;
    document.querySelectorAll('.purchase-page [data-searchable]').forEach((row) => {
        const dateText = row.dataset.searchable.match(/\b(\d{2})-(\d{2})-(\d{4})\b/);
        const date = dateText ? `${dateText[3]}-${dateText[2]}-${dateText[1]}` : null;
        const inRange = !purchaseDateRange || !date || (date >= purchaseDateRange.start && date <= purchaseDateRange.end);
        row.hidden = !row.dataset.searchable.toLowerCase().includes(query) || !inRange;
        if (!row.hidden) total++;
    });
    const message = document.querySelector('#search-message');
    message.hidden = !query && !purchaseDateRange;
    message.textContent = total ? `${total} matching preview records. Summary cards and charts show the reference month.` : 'No matching purchase records found.';
    document.querySelectorAll('.purchase-page .purchase-table').forEach((table) => {
        table.querySelector('.purchase-empty-row')?.remove();
        const visible = [...table.querySelectorAll('tbody [data-searchable]')].some((row) => !row.hidden);
        if (!visible) {
            const row = document.createElement('tr');
            row.className = 'purchase-empty-row';
            const cell = document.createElement('td');
            cell.colSpan = table.querySelectorAll('thead th').length;
            cell.textContent = 'No matching records';
            row.append(cell);
            table.querySelector('tbody').append(row);
        }
        if (table.tFoot) table.tFoot.hidden = Boolean(query || purchaseDateRange);
    });
}

function purchaseOrderDraft() {
    showPanel('Create Purchase Order');
    const form = document.createElement('form');
    form.className = 'inventory-draft-form purchase-order-form';
    let saved = {};
    try { saved = JSON.parse(localStorage.getItem('ms-purchase-order-draft') || '{}'); } catch { /* Storage may be unavailable. */ }
    for (const [labelText, key, type] of [['Supplier','supplier','text'], ['Order Date','date','date'], ['Product','product','text'], ['Quantity','quantity','number'], ['Unit Price (BDT)','price','number'], ['Expected Delivery','delivery','date']]) {
        const label = document.createElement('label');
        label.textContent = labelText;
        const input = document.createElement('input');
        input.name = key;
        input.type = type;
        input.required = true;
        input.value = saved[key] || '';
        if (type === 'number') { input.min = key === 'quantity' ? '1' : '0'; input.step = key === 'quantity' ? '1' : '0.01'; }
        label.append(input);
        form.append(label);
    }
    const note = document.createElement('p');
    note.className = 'draft-note';
    note.textContent = 'Preview mode: save a draft in this browser. Creating live purchase orders requires a connected MySQL database.';
    const status = document.createElement('span');
    status.className = 'inventory-draft-status';
    status.setAttribute('role','status');
    const save = document.createElement('button');
    save.type = 'submit';
    save.className = 'primary-button';
    save.textContent = 'Save Draft';
    form.append(note, status, save);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        try { localStorage.setItem('ms-purchase-order-draft', JSON.stringify(Object.fromEntries(new FormData(form)))); status.textContent = 'Draft saved in this browser. No live order has been created.'; }
        catch { status.textContent = 'Browser storage is unavailable. Draft could not be saved.'; }
    });
    body.append(form);
}

function purchaseRangeDialog() {
    showPanel('Purchase Date Range');
    const form = document.createElement('form');
    form.className = 'purchase-range-form';
    for (const [labelText, key, fallback] of [['From','start','2025-04-01'],['To','end','2025-04-30']]) {
        const label = document.createElement('label');
        label.textContent = labelText;
        const input = document.createElement('input');
        input.type = 'date'; input.name = key; input.required = true;
        input.value = purchaseDateRange?.[key] || fallback;
        label.append(input); form.append(label);
    }
    const note = document.createElement('p');
    note.textContent = 'Filters dated rows in the preview tables. Summary cards and charts remain on the reference month.';
    note.className = 'draft-note';
    const button = document.createElement('button');
    button.type = 'submit'; button.className = 'primary-button'; button.textContent = 'Apply Date Range';
    form.append(note,button);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const start = form.elements.start;
        const end = form.elements.end;
        end.setCustomValidity(start.value > end.value ? 'End date must be on or after start date.' : '');
        if (!form.reportValidity()) return;
        purchaseDateRange = {start:start.value,end:end.value};
        const format = (date) => new Intl.DateTimeFormat('en-GB',{day:'2-digit',month:'short',year:'numeric',timeZone:'UTC'}).format(new Date(date));
        document.querySelector('.purchase-date>span').textContent = `${format(start.value)} → ${format(end.value)}`;
        filterPurchaseTables(); dialog.close();
    });
    form.elements.end.addEventListener('input', () => form.elements.end.setCustomValidity(''));
    form.elements.start.addEventListener('input', () => form.elements.end.setCustomValidity(''));
    body.append(form);
}

document.addEventListener('click', (event) => {
    const action = event.target.closest('[data-purchase-action]')?.dataset.purchaseAction;
    if (!action) return;
    if (!document.querySelector('.purchase-page')) { window.location.assign(`/purchase?section=${encodeURIComponent(action)}`); return; }
    if (action === 'Date Range') { purchaseRangeDialog(); return; }
    if (action === 'Create Purchase Order') { purchaseOrderDraft(); return; }
    const sources = {'Purchase Order':'.purchase-orders .table-scroll','Goods Receive':'.purchase-receipts .table-scroll','Supplier':'.purchase-suppliers .table-scroll','Supplier Management':'.purchase-suppliers .table-scroll','Payment':'.purchase-payments .table-scroll','Make Payment':'.purchase-payments .table-scroll','Top Purchased Products':'.purchase-products .table-scroll'};
    const source = sources[action];
    showPanel(action, source ? document.querySelector(source) : null, 'Reference preview. Live purchase invoices, imports, landed costs and payment processing are not connected to MySQL yet.');
});


const invoiceForm = document.querySelector('#invoice-form');
const invoiceLines = document.querySelector('#invoice-lines');
const invoiceLineTemplate = invoiceLines?.querySelector('.invoice-line')?.cloneNode(true);
const invoiceMoney = (amount) => `৳ ${new Intl.NumberFormat('en-IN', {maximumFractionDigits:2}).format(amount)}`;
let salesDateRange = null;
let currentSalesTab = 'invoices';

function filterSalesRecords() {
    const query = search.value.trim().toLowerCase();
    document.querySelectorAll('.sales-page [data-searchable]').forEach((row) => {
        const date = row.dataset.date;
        row.hidden = !row.dataset.searchable.toLowerCase().includes(query) || Boolean(salesDateRange && date && (date < salesDateRange.start || date > salesDateRange.end));
    });
    const panel = document.querySelector(`#sales-panel-${currentSalesTab}`);
    if (!panel) return;
    panel.querySelector('.sales-empty-row')?.remove();
    const rows = [...panel.querySelectorAll('[data-sales-record]')];
    const visible = rows.filter((row) => !row.hidden).length;
    document.querySelector('#sales-record-count').textContent = visible ? `Showing 1 to ${visible} of ${rows.length} preview entries` : `0 of ${rows.length} preview entries`;
    if (!visible) {
        const row = document.createElement('tr'); row.className = 'sales-empty-row';
        const cell = document.createElement('td'); cell.colSpan = panel.querySelectorAll('thead th').length;
        cell.textContent = 'No matching records'; row.append(cell); panel.querySelector('tbody').append(row);
    }
    const message = document.querySelector('#search-message');
    message.hidden = !query && !salesDateRange;
    message.textContent = 'Filtered preview records. Summary cards and charts show the reference month.';
}

function selectSalesTab(key, focus = false) {
    currentSalesTab = key;
    document.querySelectorAll('[data-sales-tab]').forEach((tab) => {
        const active = tab.dataset.salesTab === key;
        tab.setAttribute('aria-selected', String(active)); tab.tabIndex = active ? 0 : -1;
        document.getElementById(tab.getAttribute('aria-controls')).hidden = !active;
        if (active && focus) tab.focus();
    });
    filterSalesRecords();
}

document.querySelectorAll('[data-sales-tab]').forEach((tab) => {
    tab.addEventListener('click', () => selectSalesTab(tab.dataset.salesTab));
    tab.addEventListener('keydown', (event) => {
        const tabs = [...document.querySelectorAll('[data-sales-tab]')];
        let next = tabs.indexOf(tab);
        if (event.key === 'ArrowRight') next = (next + 1) % tabs.length;
        else if (event.key === 'ArrowLeft') next = (next - 1 + tabs.length) % tabs.length;
        else if (event.key === 'Home') next = 0;
        else if (event.key === 'End') next = tabs.length - 1;
        else return;
        event.preventDefault(); selectSalesTab(tabs[next].dataset.salesTab, true);
    });
});

function invoiceTotals() {
    if (!invoiceForm) return null;
    let subtotal = 0;
    invoiceLines.querySelectorAll('.invoice-line').forEach((row, index) => {
        const quantity = Math.max(0, Number(row.querySelector('.line-quantity').value) || 0);
        const price = Math.max(0, Math.round((Number(row.querySelector('.line-price').value) || 0) * 100));
        const total = Math.round(quantity * price);
        subtotal += total;
        row.querySelector('.line-number').textContent = index + 1;
        row.querySelector('.line-total').textContent = invoiceMoney(total / 100);
        for (const [selector, label] of [['.line-product','Product'],['.line-quantity','Quantity'],['.line-price','Unit price'],['.remove-invoice-line','Remove item']]) row.querySelector(selector).setAttribute('aria-label', `${label} ${index + 1}`);
    });
    const discountInput = invoiceForm.elements.discount;
    discountInput.max = String(subtotal / 100);
    const discount = Math.min(subtotal, Math.max(0, Math.round((Number(discountInput.value) || 0) * 100)));
    const tax = Math.round((subtotal - discount) * .15);
    const total = subtotal - discount + tax;
    document.querySelector('#invoice-subtotal').textContent = invoiceMoney(subtotal / 100);
    document.querySelector('#invoice-tax').textContent = invoiceMoney(tax / 100);
    document.querySelector('#invoice-total').textContent = invoiceMoney(total / 100);
    return {subtotal:subtotal / 100, discount:discount / 100, tax:tax / 100, total:total / 100};
}

function addInvoiceLine(data = null) {
    const row = invoiceLineTemplate.cloneNode(true);
    row.querySelector('.line-product').selectedIndex = 0;
    if (data) row.querySelector('.line-product').value = data.product;
    row.querySelector('.line-quantity').value = data?.quantity ?? 1;
    row.querySelector('.line-price').value = data?.price ?? row.querySelector('.line-product').selectedOptions[0].dataset.price;
    invoiceLines.append(row); invoiceTotals();
    return row;
}

function invoiceDraftData() {
    return {
        fields:Object.fromEntries(new FormData(invoiceForm)),
        lines:[...invoiceLines.querySelectorAll('.invoice-line')].map((row) => ({product:row.querySelector('.line-product').value,quantity:row.querySelector('.line-quantity').value,price:row.querySelector('.line-price').value})),
    };
}
function saveInvoiceDraft() {
    const status = document.querySelector('#invoice-status');
    try { localStorage.setItem('ms-sales-invoice-draft', JSON.stringify(invoiceDraftData())); status.textContent = 'Draft saved in this browser. No live invoice has been created.'; status.classList.add('success'); return true; }
    catch { status.textContent = 'Browser storage is unavailable. The draft could not be saved.'; status.classList.remove('success'); return false; }
}

if (invoiceForm) {
    try {
        const saved = JSON.parse(localStorage.getItem('ms-sales-invoice-draft') || 'null');
        if (saved?.fields && Array.isArray(saved.lines) && saved.lines.length && saved.lines.length <= 100) {
            for (const [name,value] of Object.entries(saved.fields)) { if (invoiceForm.elements.namedItem(name)) invoiceForm.elements.namedItem(name).value = value; }
            const available = [...invoiceLineTemplate.querySelector('.line-product').options].map((option) => option.value);
            const valid = saved.lines.filter((line) => available.includes(line.product));
            if (valid.length) { invoiceLines.replaceChildren(); valid.forEach((line) => addInvoiceLine(line)); }
            document.querySelector('#invoice-status').textContent = 'Browser draft restored · Live invoices require MySQL.';
        }
    } catch { /* Invalid browser drafts leave the reference invoice in place. */ }
    invoiceTotals();
    document.querySelector('#add-invoice-item').addEventListener('click', () => { const row = addInvoiceLine(); row.querySelector('.line-product').focus(); });
    invoiceForm.addEventListener('input', () => {
        invoiceForm.elements.due.setCustomValidity('');
        invoiceTotals();
        document.querySelector('#invoice-status').classList.remove('success');
        document.querySelector('#invoice-status').textContent = 'Unsaved preview changes · Save Draft to keep a browser copy.';
    });
    invoiceLines.addEventListener('change', (event) => {
        if (event.target.matches('.line-product')) event.target.closest('tr').querySelector('.line-price').value = event.target.selectedOptions[0].dataset.price;
        invoiceTotals();
    });
    invoiceLines.addEventListener('click', (event) => {
        const remove = event.target.closest('.remove-invoice-line');
        if (!remove) return;
        if (invoiceLines.children.length === 1) { document.querySelector('#invoice-status').textContent = 'An invoice must contain at least one item.'; return; }
        remove.closest('tr').remove(); invoiceTotals(); document.querySelector('#add-invoice-item').focus();
        document.querySelector('#invoice-status').textContent = 'Item removed from this preview. Save Draft to keep the change.';
    });
    document.querySelector('#save-invoice-draft').addEventListener('click', saveInvoiceDraft);
    invoiceForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const due = invoiceForm.elements.due;
        due.setCustomValidity(due.value < invoiceForm.elements.date.value ? 'Due date must be on or after the invoice date.' : '');
        if (!invoiceForm.reportValidity()) return;
        const totals = invoiceTotals();
        saveInvoiceDraft();
        document.querySelector('#invoice-print')?.remove();
        const print = document.createElement('section'); print.id = 'invoice-print';
        const append = (tag,text,parent = print,className = '') => { const element = document.createElement(tag); element.textContent = text; element.className = className; parent.append(element); return element; };
        append('p','PREVIEW / DRAFT — not a posted invoice',print,'print-draft-label');
        append('h1','MS Corporation'); append('p','Medical Equipment Importer & Supplier'); append('h2','Sales Invoice Draft');
        append('p',`Customer: ${invoiceForm.elements.customer.value}`); append('p',`Invoice date: ${invoiceForm.elements.date.value} · Due date: ${due.value}`); append('p',`Payment terms: ${invoiceForm.elements.terms.value}`);
        const table = document.createElement('table');
        const header = document.createElement('tr'); ['Product','Quantity','Unit Price','Total'].forEach((text) => append('th',text,header)); table.append(header);
        invoiceDraftData().lines.forEach((line) => { const row = document.createElement('tr'); [line.product,line.quantity,invoiceMoney(Number(line.price)),invoiceMoney(Number(line.quantity) * Number(line.price))].forEach((text) => append('td',String(text),row)); table.append(row); });
        print.append(table);
        append('p',`Sub Total: ${invoiceMoney(totals.subtotal)}`); append('p',`Discount: ${invoiceMoney(totals.discount)}`); append('p',`VAT (15%): ${invoiceMoney(totals.tax)}`); append('strong',`Total Amount: ${invoiceMoney(totals.total)}`);
        document.body.append(print); window.print();
    });
}

function salesDateDialog() {
    showPanel('Sales Date Range');
    const form = document.createElement('form'); form.className = 'purchase-range-form';
    for (const [labelText,name,defaultValue] of [['From','start','2025-04-01'],['To','end','2025-04-30']]) {
        const label = document.createElement('label'); label.textContent = labelText;
        const input = document.createElement('input'); input.type = 'date'; input.name = name; input.required = true; input.value = salesDateRange?.[name] || defaultValue;
        label.append(input); form.append(label);
    }
    const note = document.createElement('p'); note.className = 'sales-date-note'; note.textContent = 'Filters invoice, sales order and quotation rows. Summary cards and chart show the reference month.';
    const apply = document.createElement('button'); apply.type = 'submit'; apply.className = 'primary-button'; apply.textContent = 'Apply Date Range'; form.append(note,apply);
    form.addEventListener('input', () => form.elements.end.setCustomValidity(''));
    form.addEventListener('submit', (event) => {
        event.preventDefault(); const start = form.elements.start.value; const end = form.elements.end.value;
        form.elements.end.setCustomValidity(start > end ? 'End date must be on or after the start date.' : '');
        if (!form.reportValidity()) return;
        salesDateRange = {start,end};
        const format = (date) => new Intl.DateTimeFormat('en-GB',{day:'2-digit',month:'short',year:'numeric',timeZone:'UTC'}).format(new Date(date));
        document.querySelector('.sales-date>span').textContent = `${format(start)} → ${format(end)}`;
        filterSalesRecords(); dialog.close();
    }); body.append(form);
}

document.addEventListener('click', (event) => {
    const view = event.target.closest('[data-sales-view]');
    if (view) {
        const row = view.closest('tr'); const headers = [...row.closest('table').querySelectorAll('thead th')];
        showPanel(view.dataset.salesView);
        const details = document.createElement('dl'); details.className = 'product-detail-grid';
        [...row.cells].slice(0,-1).forEach((cell,index) => { const item = document.createElement('div'); const term = document.createElement('dt'); const value = document.createElement('dd'); term.textContent = headers[index].textContent; value.textContent = cell.textContent; item.append(term,value); details.append(item); }); body.append(details);
    }
    const action = event.target.closest('[data-sales-action]')?.dataset.salesAction;
    if (!action) return;
    if (!invoiceForm) { window.location.assign('/sales'); return; }
    if (action === 'Date Range') { salesDateDialog(); return; }
    const tab = {'Sales Order':'orders','Quotation':'quotations','Invoice':'invoices','Customer':'customers'}[action];
    if (tab) { selectSalesTab(tab, true); return; }
    if (action === 'Create Invoice') { document.querySelector('#invoice-builder').scrollIntoView({block:'nearest'}); invoiceForm.elements.customer.focus(); return; }
    if (action === 'Top Selling Products') { showPanel(action,document.querySelector('.sales-top-products .table-scroll')); return; }
    if (action === 'View Price List' || action === 'Price List') {
        showPanel('Price List');
        const list = document.createElement('dl'); list.className = 'product-detail-grid';
        [...invoiceLineTemplate.querySelector('.line-product').options].forEach((option) => { const item = document.createElement('div'); const name = document.createElement('dt'); const price = document.createElement('dd'); name.textContent = option.value; price.textContent = invoiceMoney(Number(option.dataset.price)); item.append(name,price); list.append(item); }); body.append(list); return;
    }
    if (action === 'Create Sales Order') selectSalesTab('orders');
    if (action === 'Create Quotation') selectSalesTab('quotations');
    if (action === 'Add Customer') selectSalesTab('customers');
    showPanel(action,null,'This is a reference preview. Live quotation, sales order and customer creation will be available when the management module is connected to MySQL. Invoice drafts can already be edited, saved in this browser and printed.');
});


const contactDataElement = document.querySelector('#contact-preview-data');
const contactData = contactDataElement ? JSON.parse(contactDataElement.textContent) : null;
let contactMode = 'customers';
let selectedContactId = 'C-001';
const contactMoney = (value) => `৳ ${new Intl.NumberFormat('en-IN').format(value)}`;
function findContact(id) { return [...contactData.customers,...contactData.suppliers].find((contact) => contact.id === id); }
function filterContacts() {
    if (!contactData) return;
    const query = document.querySelector('#contact-search').value.trim().toLowerCase();
    const type = document.querySelector('#contact-type-filter').value;
    const balance = document.querySelector('#contact-due-filter').value;
    const panel = document.querySelector(`#contact-panel-${contactMode}`);
    panel.querySelector('.contacts-empty-row')?.remove();
    let visible = 0;
    panel.querySelectorAll('[data-contact-row]').forEach((row) => {
        const contact = findContact(row.dataset.contactRow);
        const matches = [contact.name,contact.phone,contact.email,contact.type].join(' ').toLowerCase().includes(query);
        row.hidden = !matches || Boolean(type && contact.type !== type) || (balance === 'due' && contact.due <= 0) || (balance === 'clear' && contact.due > 0);
        if (!row.hidden) visible++;
    });
    document.querySelector('#contact-result-count').textContent = visible ? `Showing 1 to ${visible} of ${contactData[contactMode].length} preview entries` : `0 of ${contactData[contactMode].length} preview entries`;
    if (!visible) { const row = document.createElement('tr'); row.className = 'contacts-empty-row'; const cell = document.createElement('td'); cell.colSpan = 8; cell.textContent = 'No matching contacts. Try another search or clear the filters.'; row.append(cell); panel.querySelector('tbody').append(row); }
}
function chooseContact(id) {
    const contact = findContact(id);
    if (!contact) return;
    selectedContactId = id;
    const supplier = contactData.suppliers.some((item) => item.id === id);
    document.querySelector('#detail-name').textContent = contact.name;
    document.querySelector('#detail-role').textContent = supplier ? 'Supplier' : 'Customer';
    const avatar = document.querySelector('#detail-avatar'); avatar.textContent = contact.initials; avatar.className = `contact-avatar ${contact.color}`;
    ['phone','email','address','contact','designation'].forEach((key) => { document.querySelector(`#detail-${key}`).textContent = contact[key] || '—'; });
    document.querySelector('#detail-orders').textContent = contact.orders;
    document.querySelector('#detail-total-label').textContent = supplier ? 'Total Purchase' : 'Total Sales';
    document.querySelector('#detail-total').textContent = contactMoney(contact.total);
    document.querySelector('#detail-due').textContent = contactMoney(contact.due);
    document.querySelectorAll('[data-contact-row]').forEach((row) => row.classList.toggle('is-selected',row.dataset.contactRow === id));
}
function switchContacts(mode, selectFirst = true) {
    contactMode = mode;
    document.querySelectorAll('[data-contact-switch]').forEach((button) => {
        const active = button.dataset.contactSwitch === mode;
        button.classList.toggle('selected',active);
        if (button.getAttribute('role') === 'tab') { button.setAttribute('aria-selected', String(active)); button.tabIndex = active ? 0 : -1; }
    });
    ['customers','suppliers'].forEach((key) => { document.querySelector(`#contact-panel-${key}`).hidden = key !== mode; });
    document.querySelectorAll('[data-contact-nav]').forEach((button) => button.classList.toggle('selected',button.dataset.contactNav.toLowerCase() === mode));
    const type = document.querySelector('#contact-type-filter'); type.replaceChildren(new Option('All Types',''));
    [...new Set(contactData[mode].map((contact) => contact.type))].forEach((value) => type.append(new Option(value,value)));
    if (selectFirst) chooseContact(contactData[mode][0].id);
    filterContacts();
}
function contactDraft(edit = false) {
    const contact = edit ? findContact(selectedContactId) : null;
    const supplier = contact ? contactData.suppliers.some((item) => item.id === contact.id) : contactMode === 'suppliers';
    showPanel(`${edit ? 'Edit' : 'Add New'} ${supplier ? 'Supplier' : 'Customer'} Draft`);
    const form = document.createElement('form'); form.className = 'contact-draft-form';
    const key = `ms-contact-draft-${contact?.id || contactMode+'-new'}`;
    let saved = {};
    try { saved = JSON.parse(localStorage.getItem(key) || '{}'); } catch { /* Keep the reference values if storage is unavailable. */ }
    for (const [labelText,name,type] of [['Company Name','name','text'],['Type','type','text'],['Phone','phone','tel'],['Email','email','email'],['Address','address','text'],['Contact Person','contact','text'],['Designation','designation','text']]) {
        const label = document.createElement('label'); label.textContent = labelText;
        const input = document.createElement('input'); input.name = name; input.type = type; input.required = ['name','type','phone'].includes(name); input.value = saved[name] ?? contact?.[name] ?? '';
        label.append(input); form.append(label);
    }
    const note = document.createElement('p'); note.textContent = 'Preview mode: this draft is saved in your browser only. Live records and account balances remain unchanged until MySQL is connected.';
    const status = document.createElement('p'); status.className = 'draft-status'; status.setAttribute('role','status');
    const button = document.createElement('button'); button.type = 'submit'; button.className = 'primary-button'; button.textContent = 'Save Draft'; form.append(note,status,button);
    form.addEventListener('submit',(event) => { event.preventDefault(); try { localStorage.setItem(key,JSON.stringify(Object.fromEntries(new FormData(form)))); status.textContent = 'Draft saved in this browser. Live contact records are unchanged.'; } catch { status.textContent = 'Browser storage is unavailable. Draft could not be saved.'; } });
    body.append(form);
}
function contactDateDialog() {
    showPanel('Recent Activities Date Range');
    const form = document.createElement('form'); form.className = 'purchase-range-form';
    for (const [labelText,name,value] of [['From','start','2025-04-01'],['To','end','2025-04-30']]) { const label = document.createElement('label'); label.textContent = labelText; const input = document.createElement('input'); input.type = 'date'; input.name = name; input.required = true; input.value = value; label.append(input); form.append(label); }
    const note = document.createElement('p'); note.className = 'draft-note'; note.textContent = 'Filters recent activities. Contact lists, summary cards and charts show reference preview data.';
    const apply = document.createElement('button'); apply.className = 'primary-button'; apply.type = 'submit'; apply.textContent = 'Apply Date Range'; form.append(note,apply);
    form.addEventListener('input',() => form.elements.end.setCustomValidity(''));
    form.addEventListener('submit',(event) => {
        event.preventDefault(); const start = form.elements.start.value; const end = form.elements.end.value;
        form.elements.end.setCustomValidity(start > end ? 'End date must be on or after the start date.' : ''); if (!form.reportValidity()) return;
        let visible = 0;
        document.querySelectorAll('[data-contact-date]').forEach((row) => { row.hidden = row.dataset.contactDate < start || row.dataset.contactDate > end; if (!row.hidden) visible++; });
        document.querySelector('#contact-activities-empty').hidden = visible > 0;
        document.querySelector('.contact-date>span').textContent = `${start} → ${end}`; dialog.close();
    }); body.append(form);
}
if (contactData) {
    search.placeholder = 'Search customers, suppliers, products, invoices, etc...';
    switchContacts('customers');
    document.querySelector('#contact-search').addEventListener('input',filterContacts);
    ['contact-type-filter','contact-due-filter'].forEach((id) => document.getElementById(id).addEventListener('change',filterContacts));
    document.querySelector('#contact-filter-toggle').addEventListener('click',(event) => { const button = event.currentTarget; const open = button.getAttribute('aria-expanded') !== 'true'; button.setAttribute('aria-expanded',String(open)); document.querySelector('#contact-filters').hidden = !open; });
    document.querySelector('#clear-contact-filters').addEventListener('click',() => { document.querySelector('#contact-type-filter').value = ''; document.querySelector('#contact-due-filter').value = ''; document.querySelector('#contact-search').value = ''; search.value = ''; filterContacts(); });
    document.querySelectorAll('.contacts-tabs [role="tab"]').forEach((tab) => tab.addEventListener('keydown',(event) => { if (['ArrowLeft','ArrowRight','Home','End'].includes(event.key)) { event.preventDefault(); const mode = event.key === 'Home' ? 'customers' : event.key === 'End' ? 'suppliers' : contactMode === 'customers' ? 'suppliers' : 'customers'; switchContacts(mode); document.querySelector(`#contact-tab-${mode}`).focus(); } }));
}
document.addEventListener('click',(event) => {
    const nav = event.target.closest('[data-contact-nav]');
    if (nav && !contactData) { window.location.assign('/customers-suppliers'); return; }
    if (!contactData) return;
    const toggle = event.target.closest('[data-contact-switch]');
    if (toggle) switchContacts(toggle.dataset.contactSwitch);
    const row = event.target.closest('[data-contact-id]');
    if (row) chooseContact(row.dataset.contactId);
    if (nav) { const mode = nav.dataset.contactNav.toLowerCase(); if (['customers','suppliers'].includes(mode)) switchContacts(mode); else showPanel('Contact Groups',null,'Reference contact groups: Hospitals, Clinics, Pharmacies, Dealers, Manufacturers, Distributors and Local Suppliers. Live group management requires MySQL.'); }
    const rank = event.target.closest('[data-contact-rank]');
    if (rank) { switchContacts(rank.dataset.contactRank); document.querySelector('#contact-search').focus(); }
    const action = event.target.closest('[data-contact-action]')?.dataset.contactAction;
    if (action === 'add') contactDraft();
    if (action === 'edit') contactDraft(true);
    if (action === 'date') contactDateDialog();
    if (action === 'activities') showPanel('Recent Activities',document.querySelector('.contact-activity-list'));
});


let accountTab = 'vouchers';
let accountRange = null;
function filterAccounting() {
    const query = search.value.trim().toLowerCase();
    document.querySelectorAll('.accounting-page [data-searchable]').forEach((row) => { row.hidden = !row.dataset.searchable.toLowerCase().includes(query) || Boolean(accountRange && (row.dataset.date < accountRange.start || row.dataset.date > accountRange.end)); });
    const panel = document.querySelector(`#account-panel-${accountTab}`);
    panel.querySelector('.account-empty-row')?.remove();
    const rows = [...panel.querySelectorAll('[data-account-record]')]; const visible = rows.filter((row) => !row.hidden).length;
    document.querySelector('#account-record-count').textContent = visible ? `Showing 1 to ${visible} of ${rows.length} preview entries` : `0 of ${rows.length} preview entries`;
    if (!visible) { const row = document.createElement('tr'); row.className = 'account-empty-row'; const cell = document.createElement('td'); cell.colSpan = 7; cell.textContent = 'No matching records'; row.append(cell); panel.querySelector('tbody').append(row); }
    document.querySelector('#account-transactions-empty').hidden = [...document.querySelectorAll('[data-account-transaction]')].some((row) => !row.hidden);
    const message = document.querySelector('#search-message'); message.hidden = !query && !accountRange; message.textContent = 'Filtered preview records. Summary cards and charts show the reference month.';
}
function selectAccountTab(key, focus = false) {
    accountTab = key;
    document.querySelectorAll('[data-account-tab]').forEach((tab) => { const selected = tab.dataset.accountTab === key; tab.setAttribute('aria-selected',String(selected)); tab.tabIndex = selected ? 0 : -1; document.getElementById(tab.getAttribute('aria-controls')).hidden = !selected; if (selected && focus) tab.focus(); });
    filterAccounting();
}
function accountRangeDialog() {
    showPanel('Accounting Date Range'); const form = document.createElement('form'); form.className = 'purchase-range-form';
    for (const [labelText,name,value] of [['From','start','2025-04-01'],['To','end','2025-04-30']]) { const label = document.createElement('label'); label.textContent = labelText; const input = document.createElement('input'); input.type = 'date'; input.name = name; input.required = true; input.value = accountRange?.[name] || value; label.append(input); form.append(label); }
    const note = document.createElement('p'); note.className = 'draft-note'; note.textContent = 'Filters dated transactions. Balances and charts remain reference preview figures.';
    const apply = document.createElement('button'); apply.type = 'submit'; apply.className = 'primary-button'; apply.textContent = 'Apply Date Range'; form.append(note,apply);
    form.addEventListener('input',() => form.elements.end.setCustomValidity(''));
    form.addEventListener('submit',(event) => { event.preventDefault(); const start = form.elements.start.value; const end = form.elements.end.value; form.elements.end.setCustomValidity(start > end ? 'End date must be on or after the start date.' : ''); if (!form.reportValidity()) return; accountRange = {start,end}; document.querySelector('.accounting-date>span').textContent = `${start} — ${end}`; filterAccounting(); dialog.close(); }); body.append(form);
}
function journalDraft() {
    showPanel('New Journal Entry Draft'); const form = document.createElement('form'); form.className = 'journal-draft-form';
    let saved = {}; try { saved = JSON.parse(localStorage.getItem('ms-journal-draft') || '{}'); } catch {}
    for (const [labelText,name,type] of [['Date','date','date'],['Reference','reference','text'],['Debit Account','debit','text'],['Credit Account','credit','text'],['Amount (BDT)','amount','number'],['Description','description','text']]) { const label = document.createElement('label'); label.textContent = labelText; const input = document.createElement('input'); input.name = name; input.type = type; input.required = true; input.value = saved[name] || ''; if(type === 'number'){input.min = '.01';input.step = '.01';} label.append(input);form.append(label); }
    const note = document.createElement('p'); note.textContent = 'Preview draft only. Saving here does not post a journal or change account balances. Live posting requires MySQL.';
    const status = document.createElement('p'); status.setAttribute('role','status'); const save = document.createElement('button'); save.type = 'submit'; save.className = 'primary-button'; save.textContent = 'Save Draft'; form.append(note,status,save);
    form.addEventListener('input',() => form.elements.credit.setCustomValidity(''));
    form.addEventListener('submit',(event) => { event.preventDefault(); form.elements.credit.setCustomValidity(form.elements.debit.value.trim().toLowerCase() === form.elements.credit.value.trim().toLowerCase() ? 'Debit and credit accounts must differ.' : ''); if(!form.reportValidity())return; try{localStorage.setItem('ms-journal-draft',JSON.stringify(Object.fromEntries(new FormData(form))));status.textContent = 'Draft saved in this browser. No journal has been posted.';}catch{status.textContent = 'Browser storage unavailable. Draft could not be saved.';} });body.append(form);
}
if(document.querySelector('.accounting-page')){
    search.placeholder = 'Search anything... (e.g., voucher, ledger, customer, supplier, report)';
    document.querySelectorAll('[data-account-tab]').forEach((tab) => { tab.addEventListener('click',() => selectAccountTab(tab.dataset.accountTab)); tab.addEventListener('keydown',(event) => {const tabs = [...document.querySelectorAll('[data-account-tab]')];let index = tabs.indexOf(tab);if(event.key === 'ArrowRight') index = (index+1)%tabs.length;else if(event.key === 'ArrowLeft') index = (index-1+tabs.length)%tabs.length;else if(event.key === 'Home')index=0;else if(event.key === 'End')index=tabs.length-1;else return;event.preventDefault();selectAccountTab(tabs[index].dataset.accountTab,true);}); });
}
document.addEventListener('click',(event) => {
    const transaction = event.target.closest('[data-account-transaction]');
    if(transaction){showPanel('Transaction Details',null,transaction.textContent.trim()+' · Reference preview, not a live ledger record.');}
    const action = event.target.closest('[data-account-action]')?.dataset.accountAction;if(!action)return;
    if(!document.querySelector('.accounting-page')){window.location.assign('/accounting');return;}
    if(action === 'Date Range'){accountRangeDialog();return;}
    if(action === 'New Journal Entry' || action === 'Journal Entry'){journalDraft();return;}
    if(action === 'Create Invoice'){window.location.assign('/sales');return;}
    if(action === 'Dashboard'){document.querySelector('.dashboard').scrollTo({top:0});return;}
    if(action === 'Bank Reconciliation'){selectAccountTab('reconcile',true);return;}
    if(action === 'Ledger'){selectAccountTab('vouchers',true);return;}
    const sources = {'Chart of Accounts':'.accounting-summary table','Bank & Cash':'.accounting-summary table','Recent Transactions':'.account-transactions-list','Financial Reports':'.accounting-bottom-grid'};
    showPanel(action,sources[action] ? document.querySelector(sources[action]) : null,'Reference preview only. Live receivables, payables, reconciliation and payment processing require a connected MySQL database.');
});


const reportElement = document.querySelector('#report-preview-data');
const reportData = reportElement ? JSON.parse(reportElement.textContent) : null;
let generatedReport = null;
function reportRecords(type) {
    const money = (value) => `৳ ${new Intl.NumberFormat('en-IN').format(value)}`;
    const key = type.toLowerCase();
    if (['customer','supplier'].includes(key)) return {headers:['Name','Type','Phone','Email','Due (BDT)'],rows:reportData[key].map((p)=>({values:[p.name,p.type,p.phone,p.email||'—',money(p.due)],party:p.name}))};
    if(key==='inventory')return {headers:['Product','Code','Brand','Category','Quantity','Unit Price','Warehouse'],rows:reportData.inventory.map((p)=>({values:[p[0],p[2],p[3],p[5],p[6],money(p[7]),p[9]],product:p[0],warehouse:p[9]}))};
    const headers = {sales:['Invoice','Date','Customer','Amount','Status'],purchase:['PO No.','Supplier','Date','Status','Amount'],accounting:['Date','Voucher','Type','Party','Description','Amount','Status']};
    if(!headers[key]) return {headers:['Notice'],rows:[]};
    return {headers:headers[key],rows:reportData[key].map((p)=>{
        const dateText=key==='sales'?p[1]:key==='purchase'?p[2]:p[0];
        const match=dateText.match(/^(\d{2})-(\d{2})-(\d{4})$/);
        const date=match?`${match[3]}-${match[2]}-${match[1]}`:new Date(`${dateText} UTC`).toISOString().slice(0,10);
        return {values:p,date,party:key==='sales'?p[2]:key==='purchase'?p[1]:p[3]};
    })};
}
function buildReport(type = null) {
    const form=document.querySelector('#report-filter-form');
    if(type)form.elements.type.value=type;
    const filters=Object.fromEntries(new FormData(form));
    form.elements.end.setCustomValidity(filters.start>filters.end?'End date must be on or after the start date.':'');
    if(!form.reportValidity())return null;
    const data=reportRecords(filters.type);
    const unsupported=[];
    if(filters.party&&!['Sales','Purchase','Accounting','Customer','Supplier'].includes(filters.type))unsupported.push('customer/supplier');
    if(filters.product&&filters.type!=='Inventory')unsupported.push('product');
    if(filters.warehouse&&filters.type!=='Inventory')unsupported.push('warehouse');
    const rows=data.rows.filter((row)=>(!row.date||(row.date>=filters.start&&row.date<=filters.end))&&(!filters.party||row.party===filters.party)&&(!filters.product||row.product===filters.product)&&(!filters.warehouse||row.warehouse===filters.warehouse));
    generatedReport={type:filters.type,headers:data.headers,rows:unsupported.length?[]:rows.map((r)=>r.values),start:filters.start,end:filters.end};
    const container=document.querySelector('#generated-report');container.hidden=false;
    document.querySelector('#generated-report-title').textContent=`${filters.type} Report · ${generatedReport.rows.length} preview records`;
    const note=unsupported.length?`The ${unsupported.join(', ')} filter cannot be resolved from this report's preview data. Choose All or use an Inventory report for product and warehouse filters.`:['Import','Custom'].includes(filters.type)?'This report needs live import data or a custom report definition. No sample records are available.':`Reference preview only · ${filters.start} to ${filters.end}. Charts and summary cards show the reference month.`;
    document.querySelector('#generated-report-note').textContent=note;
    const table=document.createElement('table');const head=document.createElement('thead');const heading=document.createElement('tr');data.headers.forEach((value)=>{const th=document.createElement('th');th.textContent=value;heading.append(th);});head.append(heading);table.append(head);
    const tbody=document.createElement('tbody');generatedReport.rows.forEach((row)=>{const tr=document.createElement('tr');row.forEach((value)=>{const td=document.createElement('td');td.textContent=value;tr.append(td);});tbody.append(tr);});
    if(!generatedReport.rows.length){const tr=document.createElement('tr');tr.className='report-no-results';const td=document.createElement('td');td.colSpan=data.headers.length;td.textContent='No matching preview records';tr.append(td);tbody.append(tr);}
    table.append(tbody);document.querySelector('#generated-report-table').replaceChildren(table);
    document.querySelector('#report-header-range').textContent=`${filters.start} — ${filters.end}`;
    document.querySelectorAll('[data-report-category]').forEach((button)=>button.classList.toggle('selected',button.dataset.reportCategory===filters.type));
    return generatedReport;
}
function downloadReport(type=null){const report=buildReport(type);if(!report)return;const rows=[['REFERENCE PREVIEW — not live data'],['Report',report.type],['Date Range',report.start,report.end],report.headers,...report.rows];const csv=rows.map((row)=>row.map((value)=>`"${String(value).replaceAll('"','""')}"`).join(',')).join('\r\n');const url=URL.createObjectURL(new Blob(['\uFEFF',csv],{type:'text/csv;charset=utf-8;'}));const a=document.createElement('a');a.href=url;a.download=`ms-${report.type.toLowerCase()}-preview-report.csv`;a.click();setTimeout(()=>URL.revokeObjectURL(url),1000);}
function filterRecentReports(){const query=search.value.trim().toLowerCase();document.querySelectorAll('[data-recent-report]').forEach((row)=>row.hidden=!row.textContent.toLowerCase().includes(query));}
if(reportData){search.placeholder='Search reports, invoices, products, customers, etc...';const form=document.querySelector('#report-filter-form');form.addEventListener('input',()=>form.elements.end.setCustomValidity(''));form.addEventListener('submit',(event)=>{event.preventDefault();if(buildReport())document.querySelector('#generated-report').scrollIntoView({block:'nearest'});});}
document.addEventListener('click',(event)=>{
 const nav=event.target.closest('[data-report-nav]');if(nav&&!reportData){window.location.assign('/reports');return;}if(!reportData)return;
 const category=event.target.closest('[data-report-category]');if(category){document.querySelector('#report-type').value=category.dataset.reportCategory;document.querySelector('#report-type').focus();}
 if(nav){const type=nav.dataset.reportNav.split(' ')[0];if(type==='All'){document.querySelector('#report-type').focus();}else{document.querySelector('#report-type').value=type;document.querySelector('#report-type').focus();}}
 const download=event.target.closest('[data-report-download]');if(download)downloadReport(download.dataset.reportDownload);
 if(event.target.closest('[data-report-export]'))downloadReport();
 const expand=event.target.closest('[data-report-expand]');if(expand){const card=expand.closest('.report-data-card');showPanel(card.querySelector('h2').textContent,card.querySelector('.table-scroll'),'Reference preview records.');}
 if(event.target.closest('[data-report-date]'))document.querySelector('[name="start"]').focus();
});
