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
        if (key === 'settings') {
            window.location.assign('/settings');
            return;
        }
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
    if (!message) { const productSearch = document.querySelector('#product-search'); if (productSearch) { productSearch.value = search.value; filterProducts(); } return; }
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
    document.querySelector('#product-result-count').textContent = matches ? `Showing 1 to ${matches} of ${productRows.length} products` : `0 of ${productRows.length} products`;
    document.querySelector('#product-empty').hidden = matches > 0;
}
document.querySelector('#product-search')?.addEventListener('input', filterProducts);
filterIds.forEach((id) => document.getElementById(id)?.addEventListener('change', filterProducts));

function exportProducts() {
    const columns = ['Product', 'Code', 'Brand', 'Model', 'Category', 'Stock Qty', 'Unit Price (BDT)', 'Status', 'Warehouse'];
    const records = productRows.filter((row) => !row.hidden).map(({dataset: p}) => [p.name, p.code, p.brand, p.model, p.category, p.quantity, p.price, p.status, p.warehouse]);
    const csv = [columns, ...records].map((record) => record.map(csvCell).join(',')).join('\r\n');
    const url = URL.createObjectURL(new Blob(['\uFEFF', csv], {type: 'text/csv;charset=utf-8;'}));
    const link = document.createElement('a');
    link.href = url;
    link.download = 'ms-corporation-inventory.csv';
    link.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

document.querySelectorAll('[data-stock-delete]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(`Delete “${form.dataset.stockDelete}”?`)) event.preventDefault();
    });
});

document.addEventListener('click', (event) => {
    const action = event.target.closest('[data-inventory-action]')?.dataset.inventoryAction;
    if (!action) return;
    if (action === 'Export Products') { exportProducts(); return; }
    if (action === 'Add New Product') { window.location.assign(event.target.closest('[data-product-create-url]').dataset.productCreateUrl); return; }
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
    message.textContent = total ? `${total} matching records. Summary cards and charts show the reference month.` : 'No matching purchase records found.';
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


const productCategory = document.querySelector('#category_id');
const productSubcategory = document.querySelector('#subcategory_id');

if (productCategory && productSubcategory) {
    const filterSubcategories = () => {
        for (const option of productSubcategory.options) {
            option.hidden = Boolean(option.value && option.dataset.parentCategory !== productCategory.value);
            option.disabled = option.hidden;
        }
        if (productSubcategory.selectedOptions[0]?.disabled) productSubcategory.value = '';
    };
    productCategory.addEventListener('change', filterSubcategories);
    filterSubcategories();
}

const purchaseInvoiceForm = document.querySelector('#purchase-invoice-form');

if (purchaseInvoiceForm) {
    const lines = document.querySelector('#purchase-invoice-lines');
    const template = lines.firstElementChild.cloneNode(true);
    const add = document.querySelector('#add-purchase-invoice-line');
    const total = document.querySelector('#purchase-invoice-total');
    const currency = new Intl.NumberFormat('en-BD', {style:'currency', currency:'BDT'});
    const labels = {product_id:'Product', quantity:'Quantity', unit_price:'Unit price'};

    function updatePurchaseTotals() {
        let grandTotal = 0;
        [...lines.rows].forEach((row, index) => {
            const fields = Object.fromEntries([...row.querySelectorAll('[data-purchase-field]')].map((field) => [field.dataset.purchaseField, field]));
            for (const [name, field] of Object.entries(fields)) {
                field.name = `items[${index}][${name}]`;
                field.setAttribute('aria-label', `${labels[name]} ${index + 1}`);
            }
            const price = Math.round((Number(fields.unit_price.value) || 0) * 100);
            const lineTotal = Math.max(0, price) * Math.max(0, Number(fields.quantity.value) || 0);
            grandTotal += lineTotal;
            row.querySelector('[data-purchase-line-total]').value = currency.format(lineTotal / 100);
            row.querySelector('[data-purchase-unit]').textContent = fields.product_id.selectedOptions[0]?.dataset.unit || '—';
            const remove = row.querySelector('[data-purchase-remove]');
            remove.disabled = lines.rows.length === 1;
            remove.setAttribute('aria-label', `Remove line ${index + 1}`);
        });
        total.value = currency.format(grandTotal / 100);
        add.disabled = lines.rows.length >= 100;
    }

    add.addEventListener('click', () => {
        const row = template.cloneNode(true);
        row.querySelector('[data-purchase-field="product_id"]').value = '';
        row.querySelector('[data-purchase-field="quantity"]').value = 1;
        row.querySelector('[data-purchase-field="unit_price"]').value = '';
        lines.append(row);
        updatePurchaseTotals();
        row.querySelector('select').focus();
    });
    lines.addEventListener('input', updatePurchaseTotals);
    lines.addEventListener('change', updatePurchaseTotals);
    lines.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-purchase-remove]');
        if (!remove || lines.rows.length <= 1) return;
        remove.closest('tr').remove();
        updatePurchaseTotals();
        add.focus();
    });
    updatePurchaseTotals();
}

const invoiceForm = document.querySelector('#invoice-form');
const invoiceLines = document.querySelector('#invoice-lines');
const invoiceLineTemplate = invoiceLines?.querySelector('.invoice-line')?.cloneNode(true);
const invoiceProducts = [...(invoiceLineTemplate?.querySelector('.line-product').options ?? [])].filter((option) => option.value);
const invoiceHome = document.querySelector('#invoice-builder-home');
const salesOverview = document.querySelector('#sales-overview-content');

function openInvoiceCanvas() {
    salesOverview.hidden = true;
    invoiceHome.hidden = false;
    invoiceHome.scrollIntoView({block:'start'});
    invoiceForm.elements.customer.focus();
}

function showSalesOverview() {
    invoiceHome.hidden = true;
    salesOverview.hidden = false;
    if (window.location.hash === '#new-invoice') history.replaceState(history.state, '', window.location.pathname + window.location.search);
}

document.querySelector('#back-to-sales')?.addEventListener('click', () => {
    showSalesOverview();
    salesOverview.querySelector('[data-sales-action="Create Invoice"]').focus();
});
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
    document.querySelector('#sales-record-count').textContent = visible ? `Showing 1 to ${visible} of ${rows.length} records` : `0 of ${rows.length} records`;
    if (!visible) {
        const row = document.createElement('tr'); row.className = 'sales-empty-row';
        const cell = document.createElement('td'); cell.colSpan = panel.querySelectorAll('thead th').length;
        cell.textContent = 'No matching records'; row.append(cell); panel.querySelector('tbody').append(row);
    }
    const message = document.querySelector('#search-message');
    message.hidden = !query && !salesDateRange;
    message.textContent = 'Filtered database records. Summary cards and charts show the reference month.';
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

function invoiceLineAmounts(quantity, price, discount) {
    const gross = Math.round(Math.max(0, Number(quantity) || 0) * Math.max(0, Math.round((Number(price) || 0) * 100)));
    const reduction = Math.min(gross, Math.max(0, Math.round((Number(discount) || 0) * 100)));
    return {gross, discount:reduction, total:gross - reduction};
}

function invoiceTotals() {
    if (!invoiceForm) return null;
    let subtotal = 0;
    invoiceLines.querySelectorAll('.invoice-line').forEach((row, index) => {
        const discountInput = row.querySelector('.line-discount');
        const {gross, total} = invoiceLineAmounts(row.querySelector('.line-quantity').value, row.querySelector('.line-price').value, discountInput.value);
        discountInput.max = String(gross / 100);
        discountInput.setAttribute('aria-label', `Discount ${index + 1} in BDT`);
        subtotal += total;
        row.querySelector('.line-number').textContent = index + 1;
        row.querySelector('.line-total').textContent = invoiceMoney(total / 100);
        for (const [selector, label] of [['.line-unit','Unit'],['.line-quantity','Quantity'],['.line-price','Unit price'],['.remove-invoice-line','Remove item']]) row.querySelector(selector).setAttribute('aria-label', `${label} ${index + 1}`);
    });
    const discountInput = invoiceForm.elements.discount;
    discountInput.max = String(subtotal / 100);
    const discount = Math.min(subtotal, Math.max(0, Math.round((Number(discountInput.value) || 0) * 100)));
    const total = subtotal - discount;
    const paidInput = invoiceForm.elements.paid;
    paidInput.max = String(total / 100);
    const paid = Math.min(total, Math.max(0, Math.round((Number(paidInput.value) || 0) * 100)));
    const balance = total - paid;
    document.querySelector('#invoice-subtotal').textContent = invoiceMoney(subtotal / 100);
    document.querySelector('#invoice-total').textContent = invoiceMoney(total / 100);
    document.querySelector('#invoice-balance').textContent = invoiceMoney(balance / 100);
    return {subtotal:subtotal / 100, discount:discount / 100, total:total / 100, paid:paid / 100, balance:balance / 100};
}

function addInvoiceLine(data = null) {
    const row = invoiceLineTemplate.cloneNode(true);
    row.querySelector('.line-product').value = data ? resolveInvoiceProduct(data)?.value ?? '' : '';
    updateInvoiceProductButton(row);
    row.querySelector('.line-unit').value = data?.unit ?? row.querySelector('.line-product').selectedOptions[0]?.dataset.unit ?? '';
    row.querySelector('.line-quantity').value = data?.quantity ?? 1;
    row.querySelector('.line-price').value = data?.price ?? row.querySelector('.line-product').selectedOptions[0]?.dataset.price ?? '';
    row.querySelector('.line-discount').value = data?.discount ?? 0;
    invoiceLines.append(row); invoiceTotals();
    return row;
}

function resolveInvoiceProduct(line) {
    if (line.productId) return invoiceProducts.find((option) => option.value === line.productId);
    const matches = invoiceProducts.filter((option) => option.textContent === line.product);
    return matches.length === 1 ? matches[0] : undefined;
}

function updateInvoiceProductButton(row) {
    const select = row.querySelector('.line-product');
    row.querySelector('.line-product-picker span').textContent = select.selectedOptions[0]?.textContent ?? 'Select Product';
    row.querySelector('.line-price').disabled = !select.value;
}

function invoiceDraftData() {
    return {
        fields:Object.fromEntries(new FormData(invoiceForm)),
        lines:[...invoiceLines.querySelectorAll('.invoice-line')].map((row) => {
            const product = row.querySelector('.line-product');
            return {productId:product.value, product:product.value ? product.selectedOptions[0].textContent : '', unit:row.querySelector('.line-unit').value.trim(), quantity:row.querySelector('.line-quantity').value, price:row.querySelector('.line-price').value, discount:row.querySelector('.line-discount').value};
        }),
    };
}
function saveInvoiceDraft() {
    const status = document.querySelector('#invoice-status');
    try { localStorage.setItem('ms-sales-invoice-draft', JSON.stringify(invoiceDraftData())); status.textContent = 'Draft saved in this browser. No live invoice has been created.'; status.classList.add('success'); return true; }
    catch { status.textContent = 'Browser storage is unavailable. The draft could not be saved.'; status.classList.remove('success'); return false; }
}

function invoiceAmountInWords(amount) {
    const small = ['zero','one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen'];
    const tens = ['','','twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety'];
    function words(number) {
        if (number < 20) return small[number];
        if (number < 100) return tens[Math.floor(number / 10)] + (number % 10 ? `-${words(number % 10)}` : '');
        for (const [size, label] of [[1e12,'trillion'],[1e9,'billion'],[1e6,'million'],[1000,'thousand'],[100,'hundred']]) {
            if (number >= size) return `${words(Math.floor(number / size))} ${label}${number % size ? ` ${words(number % size)}` : ''}`;
        }
    }
    const minorUnits = Math.round(amount * 100);
    const taka = words(Math.floor(minorUnits / 100));
    const paisa = minorUnits % 100;
    return `${taka[0].toUpperCase()}${taka.slice(1)} Taka${paisa ? ` and ${words(paisa)} Paisa` : ''} only`;
}

async function printInvoice(totals) {
    const customer = invoiceForm.elements.customer.value.trim();
    const customers = document.querySelector('#invoice-customer-options').content.querySelectorAll('span');
    const details = [...customers].find((item) => item.textContent.trim() === customer)?.dataset;
    let gross = 0;
    let lineDiscount = 0;
    const lines = invoiceDraftData().lines.map((line) => {
        const amounts = invoiceLineAmounts(line.quantity, line.price, line.discount);
        gross += amounts.gross;
        lineDiscount += amounts.discount;
        return {...line, discount:amounts.discount / 100, total:amounts.total / 100};
    });
    await renderInvoicePrint({
        number:'Draft', customer, date:invoiceForm.elements.date.value, address:details?.address, phone:details?.phone,
        subtotal:gross / 100, discount:lineDiscount / 100 + totals.discount, total:totals.total,
        paid:totals.paid, due:totals.balance, lines,
    });
}

async function renderInvoicePrint(invoice) {
    const template = document.querySelector('#invoice-print-template');
    const print = template.content.firstElementChild.cloneNode(true);
    const lines = print.querySelector('[data-print="lines"]');
    const money = new Intl.NumberFormat('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    invoice.lines.forEach((line, index) => {
        const row = lines.insertRow();
        [index + 1, line.product, line.quantity, line.unit, money.format(line.price), money.format(line.discount), money.format(line.total)]
            .forEach((value) => { row.insertCell().textContent = value; });
    });

    const values = {...invoice, words:invoiceAmountInWords(Math.abs(invoice.total))};
    for (const field of ['subtotal', 'discount', 'total', 'paid', 'due']) values[field] = money.format(invoice[field]);
    print.querySelectorAll('[data-print]:not([data-print="lines"])').forEach((field) => { field.textContent = values[field.dataset.print]; });
    for (const field of ['customer', 'address', 'phone', 'date']) {
        if (!invoice[field]) print.querySelector(`[data-print="${field}"]`).textContent = '—';
    }
    document.querySelector('#invoice-print')?.remove();
    document.body.append(print);
    const logo = print.querySelector('img');
    if (logo) await logo.decode().catch(() => {});
    window.print();
}

async function printSavedInvoice(button) {
    const status = document.querySelector('#sales-print-status');
    button.disabled = true;
    status.textContent = '';
    try {
        const response = await fetch(button.dataset.invoicePrint, {headers:{Accept:'application/json'}});
        if (!response.ok) throw new Error('Invoice unavailable');
        await renderInvoicePrint(await response.json());
    } catch {
        status.textContent = 'Could not load the saved invoice. Please try again.';
    } finally {
        button.disabled = false;
    }
}

function setupInvoiceCustomerSuggestions() {
    const input = invoiceForm.elements.customer;
    const list = document.querySelector('#invoice-customer-suggestions');
    const source = document.querySelector('#invoice-customer-options');
    const names = [...new Set([...source.content.querySelectorAll('span')].map((item) => item.textContent.trim()).filter(Boolean))];
    let activeIndex = -1;

    function closeSuggestions() {
        list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        activeIndex = -1;
    }

    function renderSuggestions() {
        closeSuggestions();
        list.replaceChildren();
        const query = input.value.trim().toLocaleLowerCase();
        if ([...query].length < 2) return;

        const matches = names.filter((name) => name.toLocaleLowerCase().includes(query)).slice(0, 12);
        matches.forEach((name, index) => {
            const item = document.createElement('li');
            item.id = `invoice-customer-option-${index}`;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');
            item.className = 'cursor-pointer rounded px-3 py-2 text-sm hover:bg-indigo-50 aria-selected:bg-indigo-100';
            item.textContent = name;
            list.append(item);
        });
        if (matches.length) {
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }
    }

    function selectCustomer(item) {
        input.value = item.textContent;
        closeSuggestions();
        input.dispatchEvent(new Event('change', {bubbles:true}));
    }

    input.addEventListener('input', renderSuggestions);
    input.addEventListener('blur', closeSuggestions);
    list.addEventListener('pointerdown', (event) => { if (event.target.closest('[role="option"]')) event.preventDefault(); });
    list.addEventListener('click', (event) => {
        const option = event.target.closest('[role="option"]');
        if (option) selectCustomer(option);
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') { closeSuggestions(); return; }
        if (event.key === 'ArrowDown' && list.hidden) renderSuggestions();
        if (list.hidden) return;
        if (event.key === 'Enter' && activeIndex !== -1) {
            event.preventDefault();
            selectCustomer(list.children[activeIndex]);
        }
        if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') return;
        event.preventDefault();
        list.children[activeIndex]?.setAttribute('aria-selected', 'false');
        activeIndex = event.key === 'ArrowDown' ? Math.min(activeIndex + 1, list.children.length - 1) : Math.max(activeIndex - 1, 0);
        const option = list.children[activeIndex];
        option.setAttribute('aria-selected', 'true');
        input.setAttribute('aria-activedescendant', option.id);
        option.scrollIntoView({block:'nearest'});
    });
}

function setupInvoiceProductPicker() {
    const picker = document.querySelector('#invoice-product-picker');
    const tree = document.querySelector('#invoice-product-tree');
    const search = document.querySelector('#invoice-product-search');
    const count = document.querySelector('#invoice-product-count');
    let trigger;

    function closePicker(focus = true) {
        picker.hidden = true;
        trigger?.setAttribute('aria-expanded', 'false');
        if (focus) trigger?.focus();
        trigger = null;
    }

    const levels = [
        ['category', 'Uncategorized'], ['subcategory', 'No subcategory'],
        ['brand', 'No brand'], ['model', 'No model'],
    ];

    function renderBranches(parent, products, depth, expand) {
        if (depth === levels.length) {
            for (const product of products) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'invoice-product-result';
                button.dataset.invoiceProductId = product.value;
                const name = document.createElement('strong');
                name.textContent = product.textContent;
                const detail = document.createElement('small');
                detail.textContent = `${product.dataset.code} · ${product.dataset.unit} · ${invoiceMoney(Number(product.dataset.price))}`;
                button.append(name, detail);
                parent.append(button);
            }
            return;
        }

        const [field, fallback] = levels[depth];
        const groups = new Map();
        for (const product of products) {
            const name = product.dataset[field].trim() || fallback;
            if (!groups.has(name)) groups.set(name, []);
            groups.get(name).push(product);
        }
        for (const [name, entries] of [...groups].sort(([a], [b]) => a.localeCompare(b))) {
            const branch = document.createElement('details');
            branch.open = expand || groups.size === 1;
            const summary = document.createElement('summary');
            const label = document.createElement('span');
            label.textContent = name;
            const badge = document.createElement('small');
            badge.textContent = entries.length;
            summary.append(label, badge);
            const children = document.createElement('div');
            children.className = 'invoice-product-branch';
            renderBranches(children, entries, depth + 1, expand);
            branch.append(summary, children);
            parent.append(branch);
        }
    }

    function renderPicker() {
        const terms = search.value.trim().toLocaleLowerCase().split(/\s+/).filter(Boolean);
        const matches = invoiceProducts.filter((product) => {
            const text = [product.textContent, product.dataset.code, ...levels.map(([field, fallback]) => product.dataset[field] || fallback)].join(' ').toLocaleLowerCase();
            return terms.every((term) => text.includes(term));
        });
        tree.replaceChildren();
        count.textContent = matches.length ? `${matches.length} ${matches.length === 1 ? 'product' : 'products'}` : 'No products found.';
        renderBranches(tree, matches, 0, terms.length > 0);
    }

    invoiceLines.addEventListener('click', (event) => {
        const button = event.target.closest('.line-product-picker');
        if (!button) return;
        if (button === trigger) { closePicker(); return; }
        closePicker(false);
        trigger = button;
        trigger.setAttribute('aria-expanded', 'true');
        picker.hidden = false;
        search.value = '';
        renderPicker();
        search.focus();
    });
    tree.addEventListener('click', (event) => {
        const choice = event.target.closest('[data-invoice-product-id]');
        if (!choice || !trigger) return;
        const select = trigger.closest('tr').querySelector('.line-product');
        select.value = choice.dataset.invoiceProductId;
        select.dispatchEvent(new Event('change', {bubbles:true}));
        closePicker();
    });
    search.addEventListener('input', renderPicker);
    search.addEventListener('keydown', (event) => { if (event.key === 'Enter') event.preventDefault(); });
    picker.addEventListener('keydown', (event) => { if (event.key === 'Escape') closePicker(); });
    document.querySelector('#close-invoice-product-picker').addEventListener('click', () => closePicker());
    document.querySelector('#back-to-sales').addEventListener('click', () => closePicker(false));
    return closePicker;
}

if (invoiceForm) {
    setupInvoiceCustomerSuggestions();
    const closeProductPicker = setupInvoiceProductPicker();
    try {
        const saved = JSON.parse(localStorage.getItem('ms-sales-invoice-draft') || 'null');
        if (saved?.fields && Array.isArray(saved.lines) && saved.lines.length && saved.lines.length <= 100) {
            for (const [name,value] of Object.entries(saved.fields)) { if (invoiceForm.elements.namedItem(name)) invoiceForm.elements.namedItem(name).value = value; }
            const valid = saved.lines.filter(resolveInvoiceProduct);
            if (valid.length) { invoiceLines.replaceChildren(); valid.forEach((line) => addInvoiceLine(line)); }
            document.querySelector('#invoice-status').textContent = 'Browser draft restored.';
        }
    } catch { /* Invalid browser drafts leave the reference invoice in place. */ }
    invoiceTotals();
    document.querySelector('#add-invoice-item').addEventListener('click', () => { const row = addInvoiceLine(); row.querySelector('.line-product-picker').click(); });
    invoiceForm.addEventListener('input', () => {
        invoiceTotals();
        document.querySelector('#invoice-status').classList.remove('success');
        document.querySelector('#invoice-status').textContent = 'Unsaved preview changes · Save Draft to keep a browser copy.';
    });
    invoiceLines.addEventListener('change', (event) => {
        if (event.target.matches('.line-product')) {
            const row = event.target.closest('tr');
            const product = event.target.selectedOptions[0];
            row.querySelector('.line-price').value = product?.dataset.price ?? '';
            row.querySelector('.line-unit').value = product?.dataset.unit ?? '';
            updateInvoiceProductButton(row);
            document.querySelector('#invoice-status').textContent = 'Unsaved preview changes · Save Draft to keep a browser copy.';
        }
        invoiceTotals();
    });
    invoiceLines.addEventListener('click', (event) => {
        const remove = event.target.closest('.remove-invoice-line');
        if (!remove) return;
        if (invoiceLines.children.length === 1) { document.querySelector('#invoice-status').textContent = 'An invoice must contain at least one item.'; return; }
        if (remove.closest('tr').querySelector('.line-product-picker').getAttribute('aria-expanded') === 'true') closeProductPicker(false);
        remove.closest('tr').remove(); invoiceTotals(); document.querySelector('#add-invoice-item').focus();
        document.querySelector('#invoice-status').textContent = 'Item removed from this preview. Save Draft to keep the change.';
    });
    document.querySelector('#save-invoice-draft').addEventListener('click', saveInvoiceDraft);
    invoiceForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const missing = [...invoiceLines.querySelectorAll('.line-product')].find((select) => !select.value);
        if (missing) {
            const button = missing.closest('tr').querySelector('.line-product-picker');
            if (button.getAttribute('aria-expanded') === 'false') button.click();
            document.querySelector('#invoice-status').textContent = 'Select a product for each invoice line.';
            return;
        }
        if (!invoiceForm.reportValidity()) return;
        const totals = invoiceTotals();
        saveInvoiceDraft();
        printInvoice(totals);
    });
    if (window.location.hash === '#new-invoice') openInvoiceCanvas();
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
    const print = event.target.closest('[data-invoice-print]');
    if (print) { printSavedInvoice(print); return; }
    const view = event.target.closest('[data-sales-view]');
    if (view) {
        const row = view.closest('tr'); const headers = [...row.closest('table').querySelectorAll('thead th')];
        showPanel(view.dataset.salesView);
        const details = document.createElement('dl'); details.className = 'product-detail-grid';
        [...row.cells].slice(0,-1).forEach((cell,index) => { const item = document.createElement('div'); const term = document.createElement('dt'); const value = document.createElement('dd'); term.textContent = headers[index].textContent; value.textContent = cell.textContent; item.append(term,value); details.append(item); }); body.append(details);
    }
    const action = event.target.closest('[data-sales-action]')?.dataset.salesAction;
    if (!action) return;
    if (!invoiceForm) { window.location.assign(action === 'Invoice' || action === 'Create Invoice' ? '/sales#new-invoice' : '/sales'); return; }
    if (action === 'Date Range') { salesDateDialog(); return; }
    if (action === 'Invoice' || action === 'Create Invoice') { openInvoiceCanvas(); return; }
    if (!invoiceHome.hidden) showSalesOverview();
    const tab = {'Sales Order':'orders','Quotation':'quotations','Customer':'customers'}[action];
    if (tab) { selectSalesTab(tab, true); return; }
    if (action === 'Top Selling Products') { showPanel(action,document.querySelector('.sales-top-products .table-scroll')); return; }
    if (action === 'View Price List' || action === 'Price List') {
        showPanel('Price List');
        const list = document.createElement('dl'); list.className = 'product-detail-grid';
        invoiceProducts.forEach((option) => { const item = document.createElement('div'); const name = document.createElement('dt'); const price = document.createElement('dd'); name.textContent = option.textContent; price.textContent = invoiceMoney(Number(option.dataset.price)); item.append(name,price); list.append(item); }); body.append(list); return;
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
    document.querySelector('#contact-result-count').textContent = visible ? `Showing 1 to ${visible} of ${contactData[contactMode].length} records` : `0 of ${contactData[contactMode].length} records`;
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
    if (action === 'add') window.location.assign(document.querySelector('#contact-tab-suppliers')?.getAttribute('aria-selected') === 'true' ? '/suppliers' : '/customers');
    if (action === 'edit') { const id = String(selectedContactId); window.location.assign((id.startsWith('S-') ? '/suppliers/' : '/customers/') + encodeURIComponent(id.slice(2)) + '/edit'); }
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
    document.querySelector('#account-record-count').textContent = visible ? `Showing 1 to ${visible} of ${rows.length} records` : `0 of ${rows.length} records`;
    if (!visible) { const row = document.createElement('tr'); row.className = 'account-empty-row'; const cell = document.createElement('td'); cell.colSpan = 7; cell.textContent = 'No matching records'; row.append(cell); panel.querySelector('tbody').append(row); }
    document.querySelector('#account-transactions-empty').hidden = [...document.querySelectorAll('[data-account-transaction]')].some((row) => !row.hidden);
    const message = document.querySelector('#search-message'); message.hidden = !query && !accountRange; message.textContent = 'Filtered database records. Summary cards and charts show the reference month.';
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
    if(action === 'Create Invoice'){window.location.assign('/sales#new-invoice');return;}
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
        const timestamp = Date.parse(`${dateText} UTC`);
        const date = match ? `${match[3]}-${match[2]}-${match[1]}` : Number.isFinite(timestamp) ? new Date(timestamp).toISOString().slice(0,10) : '';
        return {values:p,date,party:key==='sales'?p[2]:key==='purchase'?p[1]:p[3]};
    })};
}
function buildReport(type = null) {
    const form=document.querySelector('#report-filter-form');
    if(type)form.elements.type.value=type;
    const filters=Object.fromEntries(new FormData(form));
    form.elements.end.setCustomValidity(filters.start && filters.end && filters.start > filters.end ? 'End date must be on or after the start date.' : '');
    if(!form.reportValidity())return null;
    const data=reportRecords(filters.type);
    const unsupported=[];
    if(filters.party&&!['Sales','Purchase','Accounting','Customer','Supplier'].includes(filters.type))unsupported.push('customer/supplier');
    if(filters.product&&filters.type!=='Inventory')unsupported.push('product');
    if(filters.warehouse&&filters.type!=='Inventory')unsupported.push('warehouse');
    const rows=data.rows.filter((row)=>(row.date === undefined || ((!filters.start || row.date >= filters.start) && (!filters.end || (row.date && row.date <= filters.end))))&&(!filters.party||row.party===filters.party)&&(!filters.product||row.product===filters.product)&&(!filters.warehouse||row.warehouse===filters.warehouse));
    generatedReport={type:filters.type,headers:data.headers,rows:unsupported.length?[]:rows.map((r)=>r.values),start:filters.start,end:filters.end};
    const container=document.querySelector('#generated-report');container.hidden=false;
    document.querySelector('#generated-report-title').textContent=`${filters.type} Report · ${generatedReport.rows.length} records`;
    const range = filters.start || filters.end ? `${filters.start || 'Any start date'} — ${filters.end || 'Any end date'}` : 'All dates';
    const note=unsupported.length?`The ${unsupported.join(', ')} filter is unavailable for this report.`:range;
    document.querySelector('#generated-report-note').textContent=note;
    const table=document.createElement('table');const head=document.createElement('thead');const heading=document.createElement('tr');data.headers.forEach((value)=>{const th=document.createElement('th');th.textContent=value;heading.append(th);});head.append(heading);table.append(head);
    const tbody=document.createElement('tbody');generatedReport.rows.forEach((row)=>{const tr=document.createElement('tr');row.forEach((value)=>{const td=document.createElement('td');td.textContent=value;tr.append(td);});tbody.append(tr);});
    if(!generatedReport.rows.length){const tr=document.createElement('tr');tr.className='report-no-results';const td=document.createElement('td');td.colSpan=data.headers.length;td.textContent='No matching records';tr.append(td);tbody.append(tr);}
    table.append(tbody);document.querySelector('#generated-report-table').replaceChildren(table);
    document.querySelector('#report-header-range').textContent=range;
    document.querySelectorAll('[data-report-category]').forEach((button)=>button.classList.toggle('selected',button.dataset.reportCategory===filters.type));
    return generatedReport;
}
function csvCell(value) {
    const cell = String(value ?? '');
    const safeCell = typeof value === 'string' && /^[\s\uFEFF]*[=+@-]/.test(cell) ? "'" + cell : cell;
    return `"${safeCell.replaceAll('"', '""')}"`;
}

function downloadReport(type = null) {
    const report = buildReport(type);
    if (!report) return;
    const rows = [['Report', report.type], ['Date Range', report.start, report.end], report.headers, ...report.rows];
    const csv = rows.map((row) => row.map(csvCell).join(',')).join('\r\n');
    const url = URL.createObjectURL(new Blob(['\uFEFF', csv], {type: 'text/csv;charset=utf-8;'}));
    const link = document.createElement('a');
    link.href = url;
    link.download = `ms-${report.type.toLowerCase()}-report.csv`;
    link.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}
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



document.querySelector('#open-company-editor')?.addEventListener('click', () => {
    showPanel('Edit Company Information');
    body.append(document.querySelector('#company-editor-template').content.cloneNode(true));
    const form = body.querySelector('form');
    const logoPreview = form.querySelector('#company-logo-preview');
    let companyLogo = logoPreview.querySelector('img')?.getAttribute('src') ?? '';
    const logoInput = form.querySelector('#company-logo-input');
    const previewFields = form.querySelectorAll('[data-company-preview]');
    function renderCompanyPreview() {
        previewFields.forEach((field) => {
            const value = form.elements.namedItem(field.dataset.companyPreview).value.trim();
            field.textContent = value || 'Not provided';
        });
    }
    renderCompanyPreview();
    form.addEventListener('input', renderCompanyPreview);
    let logoLoading = false;
    let selectedLogoFile = null;
    let removeLogo = false;
    function renderCompanyLogo() {
        logoPreview.replaceChildren();
        if (companyLogo) { const image = document.createElement('img'); image.src = companyLogo; image.alt = 'Company logo preview'; image.addEventListener('error', () => { logoPreview.textContent = 'Logo could not be loaded. Please select it again.'; }); logoPreview.append(image); }
        else { const placeholder = document.createElement('span'); placeholder.textContent = 'No logo uploaded'; logoPreview.append(placeholder); }
    }
    renderCompanyLogo();
    logoInput.addEventListener('change', async () => {
        const file = logoInput.files[0];
        if (!file) return;
        const status = form.querySelector('#company-editor-status');
        if (!['image/png','image/jpeg','image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) { status.textContent = 'Choose a PNG, JPG or WebP image smaller than 2 MB.'; logoInput.value = ''; return; }
        logoLoading = true;
        const saveButton = form.querySelector('[type="submit"]'); saveButton.disabled = true;
        try {
            const dataUrl = await new Promise((resolve,reject) => { const reader = new FileReader(); reader.onload = () => resolve(reader.result); reader.onerror = reject; reader.readAsDataURL(file); });
            const image = await new Promise((resolve,reject) => { const image = new Image(); image.onload = () => resolve(image); image.onerror = reject; image.src = dataUrl; });
            const scale = Math.min(1,600 / Math.max(image.naturalWidth,image.naturalHeight));
            const canvas = document.createElement('canvas'); canvas.width = Math.max(1,Math.round(image.naturalWidth * scale)); canvas.height = Math.max(1,Math.round(image.naturalHeight * scale)); canvas.getContext('2d').drawImage(image,0,0,canvas.width,canvas.height);
            selectedLogoFile = file; removeLogo = false;
            companyLogo = canvas.toDataURL('image/png'); renderCompanyLogo(); status.textContent = 'Logo selected. Click Save Changes to save it.';
        } catch { status.textContent = 'This image could not be opened. Try another file.'; logoInput.value = ''; }
        finally { logoLoading = false; saveButton.disabled = false; }
    });
    form.querySelector('#company-logo-remove').addEventListener('click', () => { if (logoLoading) return; companyLogo = ''; selectedLogoFile = null; removeLogo = true; logoInput.value = ''; renderCompanyLogo(); form.querySelector('#company-editor-status').textContent = 'Logo removed from the form. Click Save Changes to save.'; });
    form.querySelector('.company-editor-cancel').addEventListener('click', () => dialog.close());
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (logoLoading) return;
        const data = Object.fromEntries([...new FormData(form)].map(([key,value]) => [key,typeof value === 'string' ? value.trim() : value]));
        const payload = new FormData();
        Object.entries(data).forEach(([key,value]) => payload.append(key,value));
        payload.append('remove_logo',removeLogo ? '1' : '0');
        if (selectedLogoFile) payload.append('logo',selectedLogoFile);
        form.elements.name.setCustomValidity(data.name ? '' : 'Please enter a company name.');
        if (!form.reportValidity()) return;
        const status = form.querySelector('#company-editor-status');
        const saveButton = form.querySelector('[type="submit"]');
        saveButton.disabled = true;
        status.textContent = 'Saving company information...';
        try {
            const response = await fetch(form.action, {method:'POST',headers:{'Accept':'application/json'},body:payload});
            const result = await response.json();
            if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : 'Could not save company information. Please try again.');
            status.textContent = 'Company information saved to the database.';
            window.location.reload();
        } catch (error) { status.textContent = error.message || 'Unable to save. Check your connection and try again.'; }
        finally { saveButton.disabled = false; }
    });
    form.elements.name.addEventListener('input', () => form.elements.name.setCustomValidity(''));
    form.elements.name.focus();
});




