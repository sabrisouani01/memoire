/* ===================================================
   WISE TECH — i18n for inner pages
   (orders, repairs, checkout)
   Reads lang from localStorage, applies translations
   =================================================== */

/* Import translations from i18n.js if already loaded,
   otherwise define a minimal subset */
const PAGE_TRANSLATIONS = {
  ar: {
    dir: 'rtl',
    /* Nav */
    nav_home: 'الرئيسية', nav_orders: 'طلباتي', nav_repairs: 'الصيانة', nav_logout: 'تسجيل الخروج',
    /* Orders page */
    orders_title: 'طلباتي', orders_subtitle: 'تابع حالة طلباتك وإدارة مشترياتك',
    orders_total: 'إجمالي الطلبات', orders_pending: 'قيد الانتظار', orders_delivered: 'تم التسليم', orders_spent: 'إجمالي الإنفاق',
    orders_list: 'قائمة الطلبات', orders_empty: 'لا توجد طلبات', orders_empty_sub: 'ابدأ التسوق الآن!',
    orders_browse: 'تصفح المنتجات',
    col_order: 'رقم الطلب', col_products: 'المنتجات', col_amount: 'المبلغ', col_status: 'الحالة',
    col_date: 'تاريخ الطلب', col_warranty: 'نهاية الضمان', col_actions: 'الإجراءات',
    status_pending: 'قيد الانتظار', status_processing: 'قيد المعالجة', status_shipped: 'تم الشحن',
    status_delivered: 'تم التسليم', status_cancelled: 'ملغى',
    btn_delete: 'حذف', btn_locked: 'غير متاح', btn_maintenance: 'طلب صيانة', btn_home: 'الرئيسية',
    confirm_delete: 'هل أنت متأكد من إلغاء وحذف هذا الطلب؟\nلا يمكن التراجع عن هذا الإجراء.',
    sidebar_nav: 'التنقل', sidebar_summary: 'ملخص الطلبات', sidebar_account: 'الحساب',
    currency: 'دج',
    /* Repairs page */
    repairs_title: 'الصيانة', repairs_subtitle: 'إدارة طلبات الصيانة',
    repairs_total: 'إجمالي الطلبات', repairs_pending_r: 'قيد المعالجة', repairs_completed: 'مكتملة',
    /* Checkout */
    checkout_title: 'إتمام الطلب', checkout_subtitle: 'أدخل بيانات التوصيل واختر طريقة الدفع',
    delivery_title: 'معلومات التوصيل', payment_title: 'طريقة الدفع',
    address_label: 'عنوان التسليم', phone_label: 'رقم الهاتف',
    summary_title: 'ملخص الطلب', qty_label: 'الكمية',
    subtotal: 'المجموع الجزئي', delivery_fee: 'رسوم التوصيل', free: 'مجاني',
    grand_total: 'المجموع الكلي', btn_confirm: 'تأكيد وإتمام الشراء', secure_note: 'طلبك محمي وآمن بالكامل',
    step_cart: 'السلة', step_order: 'إتمام الطلب', step_confirm: 'التأكيد',
    back: 'العودة',
        repairs_new_req: 'طلب صيانة', repairs_new: 'طلب جديد', repairs_history: 'السجل',
    repairs_new_card: 'تقديم طلب صيانة جديد', repairs_history_card: 'طلبات الصيانة السابقة',
    repairs_tab_internal: 'منتجات من الموقع', repairs_tab_external: 'منتجات من المتجر',
    repairs_submit: 'إرسال طلب الصيانة', repairs_delivered_items: 'منتجات مُسلَّمة',
    select_color: 'يرجى اختيار لون',
  },
  fr: {
    dir: 'ltr',
    nav_home: 'Accueil', nav_orders: 'Mes Commandes', nav_repairs: 'Réparations', nav_logout: 'Déconnexion',
    orders_title: 'Mes Commandes', orders_subtitle: 'Suivez l\'état de vos commandes',
    orders_total: 'Total Commandes', orders_pending: 'En attente', orders_delivered: 'Livré', orders_spent: 'Total dépensé',
    orders_list: 'Liste des commandes', orders_empty: 'Aucune commande', orders_empty_sub: 'Commencez vos achats!',
    orders_browse: 'Parcourir les produits',
    col_order: 'N° Commande', col_products: 'Produits', col_amount: 'Montant', col_status: 'Statut',
    col_date: 'Date', col_warranty: 'Fin de garantie', col_actions: 'Actions',
    status_pending: 'En attente', status_processing: 'En traitement', status_shipped: 'Expédié',
    status_delivered: 'Livré', status_cancelled: 'Annulé',
    btn_delete: 'Supprimer', btn_locked: 'Indisponible', btn_maintenance: 'Demander réparation', btn_home: 'Accueil',
    confirm_delete: 'Annuler et supprimer cette commande?\nCette action est irréversible.',
    sidebar_nav: 'Navigation', sidebar_summary: 'Résumé', sidebar_account: 'Compte',
    currency: 'DA',
    repairs_title: 'Réparations', repairs_subtitle: 'Gérer vos demandes de réparation',
    repairs_total: 'Total', repairs_pending_r: 'En cours', repairs_completed: 'Terminées',
    checkout_title: 'Finaliser la commande', checkout_subtitle: 'Entrez vos informations de livraison',
    delivery_title: 'Informations de livraison', payment_title: 'Mode de paiement',
    address_label: 'Adresse de livraison', phone_label: 'Numéro de téléphone',
    summary_title: 'Résumé de commande', qty_label: 'Qté',
    subtotal: 'Sous-total', delivery_fee: 'Frais de livraison', free: 'Gratuit',
    grand_total: 'Total', btn_confirm: 'Confirmer et payer', secure_note: 'Votre commande est sécurisée',
    step_cart: 'Panier', step_order: 'Commande', step_confirm: 'Confirmation',
    back: 'Retour',
        repairs_new_req: 'Réparation', repairs_new: 'Nouvelle demande', repairs_history: 'Historique',
    repairs_new_card: 'Soumettre une demande de réparation', repairs_history_card: 'Historique des réparations',
    repairs_tab_internal: 'Produits du site', repairs_tab_external: 'Produits du magasin',
    repairs_submit: 'Envoyer la demande', repairs_delivered_items: 'Produits livrés',
    select_color: 'Veuillez sélectionner une couleur',
  },
  en: {
    dir: 'ltr',
    nav_home: 'Home', nav_orders: 'My Orders', nav_repairs: 'Repairs', nav_logout: 'Sign Out',
    orders_title: 'My Orders', orders_subtitle: 'Track and manage your orders',
    orders_total: 'Total Orders', orders_pending: 'Pending', orders_delivered: 'Delivered', orders_spent: 'Total Spent',
    orders_list: 'Orders List', orders_empty: 'No Orders', orders_empty_sub: 'Start shopping now!',
    orders_browse: 'Browse Products',
    col_order: 'Order #', col_products: 'Products', col_amount: 'Amount', col_status: 'Status',
    col_date: 'Order Date', col_warranty: 'Warranty End', col_actions: 'Actions',
    status_pending: 'Pending', status_processing: 'Processing', status_shipped: 'Shipped',
    status_delivered: 'Delivered', status_cancelled: 'Cancelled',
    btn_delete: 'Delete', btn_locked: 'Locked', btn_maintenance: 'Request Repair', btn_home: 'Home',
    confirm_delete: 'Cancel and delete this order?\nThis action cannot be undone.',
    sidebar_nav: 'Navigation', sidebar_summary: 'Order Summary', sidebar_account: 'Account',
    currency: 'DZD',
    repairs_title: 'Repairs', repairs_subtitle: 'Manage your repair requests',
    repairs_total: 'Total', repairs_pending_r: 'In Progress', repairs_completed: 'Completed',
    checkout_title: 'Checkout', checkout_subtitle: 'Enter delivery info and choose payment method',
    delivery_title: 'Delivery Information', payment_title: 'Payment Method',
    address_label: 'Delivery Address', phone_label: 'Phone Number',
    summary_title: 'Order Summary', qty_label: 'Qty',
    subtotal: 'Subtotal', delivery_fee: 'Delivery Fee', free: 'Free',
    grand_total: 'Grand Total', btn_confirm: 'Confirm & Place Order', secure_note: 'Your order is fully protected',
    step_cart: 'Cart', step_order: 'Order', step_confirm: 'Confirmation',
    back: 'Back',
        repairs_new_req: 'Repair Request', repairs_new: 'New Request', repairs_history: 'History',
    repairs_new_card: 'Submit a Repair Request', repairs_history_card: 'Repair History',
    repairs_tab_internal: 'Site Products', repairs_tab_external: 'Store Products',
    repairs_submit: 'Submit Request', repairs_delivered_items: 'Delivered Products',
    select_color: 'Please select a color',
  }
};

function tp(key) {
  const lang = localStorage.getItem('wt_lang') || 'ar';
  return PAGE_TRANSLATIONS[lang]?.[key] || PAGE_TRANSLATIONS['ar'][key] || key;
}

function applyPageLang() {
  const lang = localStorage.getItem('wt_lang') || 'ar';
  const dir  = PAGE_TRANSLATIONS[lang].dir;

  document.documentElement.lang = lang;
  document.documentElement.dir  = dir;

  /* translate all data-i18n-page elements */
  document.querySelectorAll('[data-i18n-page]').forEach(el => {
    const key = el.getAttribute('data-i18n-page');
    if (PAGE_TRANSLATIONS[lang]?.[key] !== undefined) el.innerHTML = PAGE_TRANSLATIONS[lang][key];
  });

  /* update lang buttons */
  document.querySelectorAll('.lang-btn-page').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.lang === lang);
  });
}

function buildLangSwitcher(containerId) {
  const el = document.getElementById(containerId);
  if (!el) return;
  const lang = localStorage.getItem('wt_lang') || 'ar';
  el.innerHTML = ['ar','fr','en'].map(l =>
    `<button class="lang-btn lang-btn-page${l === lang ? ' active' : ''}" data-lang="${l}">${l.toUpperCase()}</button>`
  ).join('');
  el.addEventListener('click', e => {
    const btn = e.target.closest('.lang-btn-page');
    if (!btn) return;
    localStorage.setItem('wt_lang', btn.dataset.lang);
    applyPageLang();
  });
}

document.addEventListener('DOMContentLoaded', () => {
  buildLangSwitcher('lang-switcher-page');
  applyPageLang();
});
