document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("create_product_modal");
    const openBtn = document.getElementById("btn_open_create_product_modal");
    const closeBtn = document.getElementById("btn_close_product_modal");
    const cancelBtn = document.getElementById("btn_cancel_product_modal");
    const form = document.getElementById("form_create_product");
    const toast = document.getElementById("product_toast");
    const toastMsg = document.getElementById("product_toast_msg");
    const productsGrid = document.querySelector(".products-grid");
    const totalCountText = document.getElementById("total_products_count");
    const imageInput = document.getElementById("product_image_input");
    const previewBox = document.getElementById("product_image_preview_box");
    const previewImg = document.getElementById("product_image_preview_img");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

    function openModal() {
        if (!modal) return;
        modal.classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove("active");
        document.body.style.overflow = "";
        if (form) form.reset();
        if (previewBox) previewBox.style.display = "none";
        if (previewImg) previewImg.src = "";
    }

    if (openBtn) openBtn.addEventListener("click", openModal);
    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeModal);

    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) closeModal();
        });
    }

    // Live Image Upload Preview
    if (imageInput && previewBox && previewImg) {
        imageInput.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewBox.style.display = "block";
                };
                reader.readAsDataURL(file);
            } else {
                previewBox.style.display = "none";
                previewImg.src = "";
            }
        });
    }

    function showToast(message) {
        if (!toast || !toastMsg) return;
        toastMsg.textContent = message;
        toast.classList.add("show");
        setTimeout(() => {
            toast.classList.remove("show");
        }, 3500);
    }

    function updateProductCount() {
        if (!productsGrid || !totalCountText) return;
        const count = productsGrid.querySelectorAll(".product-card").length;
        totalCountText.textContent = `Total Products: ${count} Active Items`;
    }

    // -------------------------------------------------------------
    // AJAX ADD PRODUCT (WITH FILE UPLOAD TO POSTGRESQL DB)
    // -------------------------------------------------------------
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken || "",
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const product = data.product;

                    let stockBadge = "";
                    if (product.stock_quantity <= 0) {
                        stockBadge = `<span class="status-badge status-danger">Out of Stock (0)</span>`;
                    } else if (product.stock_quantity < 10) {
                        stockBadge = `<span class="status-badge status-warning">Low Stock (${product.stock_quantity})</span>`;
                    } else {
                        stockBadge = `<span class="status-badge status-success">In Stock (${product.stock_quantity})</span>`;
                    }

                    let previewMediaHtml = "";
                    if (product.image_path) {
                        previewMediaHtml = `<img src="/${product.image_path}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover;">`;
                    } else {
                        const catLower = (product.category_name || '').toLowerCase();
                        const nameLower = (product.name || '').toLowerCase();
                        let svgIconHtml = `<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="var(--accent-brown, #4d2c20)" stroke-width="1.8"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>`;

                        if (catLower.includes('viennoiserie') || nameLower.includes('croissant')) {
                            svgIconHtml = `<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="var(--accent-brown, #4d2c20)" stroke-width="1.8"><path d="M6 13.8a4.5 4.5 0 1 1 2.6-8.3 5 5 0 0 1 6.8 0 4.5 4.5 0 1 1 2.6 8.3v4.2H6v-4.2z"/><path d="M6 18h12v2H6z"/></svg>`;
                        } else if (catLower.includes('cake') || nameLower.includes('cake')) {
                            svgIconHtml = `<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="var(--accent-brown, #4d2c20)" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M12 3v4"/></svg>`;
                        } else if (catLower.includes('pastries') || nameLower.includes('donut')) {
                            svgIconHtml = `<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="var(--accent-brown, #4d2c20)" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/></svg>`;
                        } else if (catLower.includes('beverage') || nameLower.includes('coffee') || nameLower.includes('latte')) {
                            svgIconHtml = `<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="var(--accent-brown, #4d2c20)" stroke-width="1.8"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/></svg>`;
                        }
                        previewMediaHtml = svgIconHtml;
                    }

                    const card = document.createElement("div");
                    card.className = "product-card new-row-anim";
                    card.id = `product_card_${product.id}`;
                    card.innerHTML = `
                        <div class="product-card-preview">
                            ${previewMediaHtml}
                            <button type="button" class="btn-delete-product-icon btn-delete-product" data-id="${product.id}" title="Delete Product">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        <div class="product-card-body">
                            <div class="product-card-title">${product.name}</div>
                            <div class="product-category-tag">Category: ${product.category_name}</div>
                            <p class="product-description-text">${product.description || 'No description provided.'}</p>
                            <div class="product-card-footer">
                                <span class="product-price-badge">$${parseFloat(product.price).toFixed(2)}</span>
                                ${stockBadge}
                            </div>
                        </div>
                    `;

                    productsGrid.prepend(card);
                    bindDeleteButtons();
                    updateProductCount();
                    closeModal();
                    showToast(`Product "${product.name}" saved successfully.`);
                } else {
                    alert(data.message || "Failed to save product.");
                }
            } catch (err) {
                console.error("Product Save Error:", err);
                alert("Failed to save product. Please try again.");
            }
        });
    }

    // -------------------------------------------------------------
    // AJAX DELETE PRODUCT FROM DATABASE
    // -------------------------------------------------------------
    function bindDeleteButtons() {
        const deleteBtns = document.querySelectorAll(".btn-delete-product");
        deleteBtns.forEach((btn) => {
            btn.onclick = function () {
                const id = this.getAttribute("data-id");
                window.showConfirmDialog({
                    title: 'Confirm Deletion',
                    message: 'Are you sure you want to delete this product?',
                    confirmText: 'Yes, Delete',
                    isDanger: true,
                    onConfirm: async function() {
                        try {
                            const response = await fetch(`/admin/products/${id}`, {
                                method: "DELETE",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Accept": "application/json",
                                    "X-CSRF-TOKEN": csrfToken || "",
                                },
                            });

                            const data = await response.json();

                            if (response.ok && data.success) {
                                const card = document.getElementById(`product_card_${id}`);
                                if (card) card.remove();
                                updateProductCount();
                                showToast("Product deleted successfully.");
                            } else {
                                alert(data.message || "Failed to delete product.");
                            }
                        } catch (err) {
                            console.error("Delete Error:", err);
                            alert("Failed to delete product. Please try again.");
                        }
                    }
                });
            };
        });
    }

    bindDeleteButtons();
    updateProductCount();
});
