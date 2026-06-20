/*--------- 1. Header Search & Filter ---------*/
document.addEventListener('DOMContentLoaded', () => {
    const filterToggle = document.getElementById('filterToggle');
    const filterMenu = document.getElementById('filterMenu');
    const searchInput = document.getElementById('searchInput');
    const categoryFilters = document.querySelectorAll('.category-filter');
    const campaignsGrid = document.querySelector('.campaigns-grid');

    // Toggle filter menu
    if (filterToggle && filterMenu) {
        filterToggle.addEventListener('click', (e) => {
            e.stopPropagation(); 
            filterMenu.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!filterMenu.contains(e.target) && !filterToggle.contains(e.target)) {
                filterMenu.classList.remove('show');
            }
        });
    }

    // Search campaigns and filter
    function updateResults() {
        if (!campaignsGrid) return; // Only on homepage

        const search = searchInput ? searchInput.value : '';
        const selectedCategories = Array.from(categoryFilters)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        const params = new URLSearchParams();
        if (search) params.append('search', search);
        selectedCategories.forEach(id => params.append('categories[]', id));

        fetch(`../php/fetch_campaigns.php?${params.toString()}`)
            .then(response => response.text())
            .then(html => {
                campaignsGrid.innerHTML = html;
            });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            // If not on homepage, redirect on enter or just wait for homepage logic
            if (!campaignsGrid) {
                // Not on homepage, handled by form submit
            } else {
                updateResults();
            }
        });
    }

    categoryFilters.forEach(filter => {
        filter.addEventListener('change', updateResults);
    });

    // Handle global search redirect
    const searchForm = document.getElementById('searchForm');
    if (searchForm && !campaignsGrid) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const search = searchInput.value;
            window.location.href = `homepage.php?search=${encodeURIComponent(search)}`;
        });
    }

/*--------- 2. Quick Price ---------*/
    window.setAmount = function(amount) {
        const donationInput = document.getElementById('donationAmount');
        if (donationInput) {
            donationInput.value = amount;
        }
    };

/*--------- 3. My Campaigns Tabs ---------*/
    const statusBtns = document.querySelectorAll('.campaign-status-buttons .status-btn');
    const contentBoxes = document.querySelectorAll('.campaign-content-box');

    if (statusBtns.length > 0 && contentBoxes.length > 0) {
        statusBtns.forEach((btn, index) => {
            btn.addEventListener('click', () => {
                statusBtns.forEach(b => b.style.backgroundColor = 'var(--white)');
                statusBtns.forEach(b => b.style.color = 'var(--primary)');
                contentBoxes.forEach(box => box.classList.remove('active-content'));
                btn.style.backgroundColor = 'var(--primary)';
                btn.style.color = 'var(--white)';
                
                if (contentBoxes[index]) {
                    contentBoxes[index].classList.add('active-content');
                }
            });
        });
    }

/*--------- 4. sign-up page ---------*/
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
            const pass = document.getElementById('passwordInput').value;
            const confirmPass = document.getElementById('confirmPasswordInput').value;

            if (pass !== confirmPass) {
                e.preventDefault(); 
                alert('عذراً، كلمتي المرور غير متطابقتين. الرجاء التأكد منها.');
            }
        });
    }

/*--------- 5. Cart Page ---------*/
    const cartCards = document.querySelectorAll('.cart-item-card');
    
    if (cartCards.length > 0) {
        cartCards.forEach(card => {
            const minusBtn = card.querySelectorAll('.quantity-btn')[0];
            const plusBtn = card.querySelectorAll('.quantity-btn')[1];
            const qtyVal = card.querySelector('.quantity-value');
            const removeBtn = card.querySelector('.remove-btn');

            if (plusBtn && qtyVal) {
                plusBtn.addEventListener('click', () => {
                    qtyVal.textContent = parseInt(qtyVal.textContent) + 1;
                    updateCartSummary();
                });
            }
            if (minusBtn && qtyVal) {
                minusBtn.addEventListener('click', () => {
                    let currentQty = parseInt(qtyVal.textContent);
                    if (currentQty > 1) {
                        qtyVal.textContent = currentQty - 1;
                        updateCartSummary();
                    }
                });
            }

  
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    card.remove();
                    updateCartSummary();
                });
            }
        });
    }

    function updateCartSummary() {
        const cartCards = document.querySelectorAll('.cart-item-card');
        let totalCampaigns = cartCards.length;
        let totalPrice = 0;

        cartCards.forEach(card => {
            const qtyElement = card.querySelector('.quantity-value');
            const qty = qtyElement ? parseInt(qtyElement.textContent) : 1;
            const priceElement = card.querySelector('.cart-item-amount span');

            if (priceElement) {
                const priceText = priceElement.textContent.replace(/,/g, '').replace(/[^\d.]/g, '');
                const price = parseFloat(priceText) || 0;
                totalPrice += (price * qty);
            }
        });

        const countDisplay = document.querySelector('.cart-items-count'); 
        const summarySpans = document.querySelectorAll('.summary-row span'); 
        
        if (countDisplay) countDisplay.textContent = 'عدد الحملات: ' + totalCampaigns;
        if (summarySpans[1]) summarySpans[1].textContent = totalCampaigns;

        const formattedTotal = totalPrice.toLocaleString('en-US');
        const riyalImg = '<img src="../images/Saudi_Riyal_Symbol.png" class="riyal-icon" alt="ريال">';

        if (summarySpans[3]) summarySpans[3].innerHTML = formattedTotal + ' ' + riyalImg;
        if (summarySpans[5]) summarySpans[5].innerHTML = formattedTotal + ' ' + riyalImg;
    }
});