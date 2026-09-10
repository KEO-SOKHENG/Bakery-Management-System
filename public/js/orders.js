document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("create_order_modal");
    const openModalBtn = document.getElementById("btn_open_create_order_modal");
    const closeModalBtn = document.getElementById("btn_close_order_modal");
    const cancelModalBtn = document.getElementById("btn_cancel_order_modal");
    const form = document.getElementById("form_create_order");

    const orderTypeSelect = document.getElementById("order_type_input");
    const customerNameInput = document.getElementById("customer_name_input");

    const toast = document.getElementById("order_toast");
    const toastMsg = document.getElementById("order_toast_msg");

    const filterBtns = document.querySelectorAll(".filter-btn");
    const tableBody = document.querySelector(".orders-table tbody");

    const viewModal = document.getElementById("view_order_modal");
    const closeViewBtn = document.getElementById("btn_close_view_modal");

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
        if (customerNameInput) customerNameInput.value = "Walk-in Customer";
    }

    function showToast(message) {
        if (!toast || !toastMsg) return;
        toastMsg.textContent = message;
        toast.classList.add("show");
        setTimeout(() => {
            toast.classList.remove("show");
        }, 3500);
    }

    if (openModalBtn) openModalBtn.addEventListener("click", openModal);
    if (closeModalBtn) closeModalBtn.addEventListener("click", closeModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener("click", closeModal);

    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) closeModal();
        });
    }

    if (closeViewBtn) {
        closeViewBtn.addEventListener("click", () => {
            if (viewModal) viewModal.classList.remove("active");
            document.body.style.overflow = "";
        });
    }

    if (viewModal) {
        viewModal.addEventListener("click", (e) => {
            if (e.target === viewModal) {
                viewModal.classList.remove("active");
                document.body.style.overflow = "";
            }
        });
    }

    // Auto-fill "Walk-in Customer" when Store POS is selected
    if (orderTypeSelect && customerNameInput) {
        orderTypeSelect.addEventListener("change", function () {
            if (this.value === "Store POS") {
                customerNameInput.value = "Walk-in Customer";
            } else if (customerNameInput.value === "Walk-in Customer") {
                customerNameInput.value = "";
                customerNameInput.focus();
            }
        });
    }

    // -------------------------------------------------------------
    // AJAX POST ORDER CREATION
    // -------------------------------------------------------------
    if (form) {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();

            const actionUrl = this.getAttribute("action");
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            const orderType = document.getElementById("order_type_input").value;
            const customerName = document.getElementById("customer_name_input").value;
            const itemsSummary = document.getElementById("items_summary_input").value;
            const totalPrice = document.getElementById("total_price_input").value;
            const status = document.getElementById("order_status_input").value;

            try {
                const response = await fetch(actionUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken || "",
                    },
                    body: JSON.stringify({
                        order_type: orderType,
                        customer_name: customerName,
                        items_summary: itemsSummary,
                        total_amount: totalPrice,
                        status: status,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const order = data.order;
                    const statusClass = (order.status || "pending").toLowerCase();

                    let statusBadgeHtml = `<span class="status-badge status-danger">Pending</span>`;
                    if (statusClass === "completed") {
                        statusBadgeHtml = `<span class="status-badge status-success">Completed</span>`;
                    } else if (statusClass === "baking" || statusClass === "in baking") {
                        statusBadgeHtml = `<span class="status-badge status-warning">In Baking</span>`;
                    } else if (statusClass === "ready" || statusClass === "ready for pickup") {
                        statusBadgeHtml = `<span class="status-badge status-info">Ready for Pickup</span>`;
                    }

                    const tr = document.createElement("tr");
                    tr.className = "new-row-anim";
                    tr.id = `order_row_${order.id}`;
                    tr.setAttribute("data-status", statusClass);
                    tr.innerHTML = `
                        <td style="font-weight: 700;">${order.order_number}</td>
                        <td>${order.customer_name}</td>
                        <td>${order.order_type}</td>
                        <td>${order.items_summary}</td>
                        <td style="font-weight: 700;">$${parseFloat(order.total_amount).toFixed(2)}</td>
                        <td>${statusBadgeHtml}</td>
                        <td>
                            <div class="order-action-btns">
                                <button type="button" class="btn-icon-action btn-view-order" data-order='${JSON.stringify(order)}' title="View Details">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button type="button" class="btn-icon-action btn-delete-order" data-id="${order.id}" title="Delete Order">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    `;

                    const emptyRow = tableBody ? tableBody.querySelector("td[colspan='7']") : null;
                    if (emptyRow) {
                        emptyRow.closest("tr").remove();
                    }

                    if (tableBody) tableBody.prepend(tr);
                    closeModal();
                    showToast(`Order ${order.order_number} saved to PostgreSQL database!`);
                } else {
                    alert(data.message || "Failed to save order to database.");
                }
            } catch (err) {
                console.error("Database Save Error:", err);
                alert("Error connecting to database. Please check PostgreSQL server.");
            }
        });
    }

    // -------------------------------------------------------------
    // GLOBAL EVENT DELEGATION FOR VIEW & DELETE BUTTONS
    // -------------------------------------------------------------
    document.addEventListener("click", async (e) => {
        // VIEW ORDER DETAILS
        const viewBtn = e.target.closest(".btn-view-order");
        if (viewBtn) {
            e.preventDefault();
            const rawData = viewBtn.getAttribute("data-order");
            if (!rawData) return;
            try {
                const order = typeof rawData === "string" ? JSON.parse(rawData) : rawData;
                document.getElementById("view_order_id").textContent = order.order_number || `#ORD-${order.id}`;
                document.getElementById("view_order_customer").textContent = order.customer_name || "Walk-in Customer";
                document.getElementById("view_order_type").textContent = order.order_type || "Store POS";
                document.getElementById("view_order_items").textContent = order.items_summary || "Assorted Bakery Items";
                document.getElementById("view_order_total").textContent = `$${parseFloat(order.total_amount || 0).toFixed(2)}`;
                document.getElementById("view_order_status").textContent = (order.status || "completed").toUpperCase();

                if (viewModal) {
                    viewModal.classList.add("active");
                    document.body.style.overflow = "hidden";
                }
            } catch (err) {
                console.error("View Order Parse Error:", err);
            }
        }

        // DELETE ORDER
        const deleteBtn = e.target.closest(".btn-delete-order");
        if (deleteBtn) {
            e.preventDefault();
            const id = deleteBtn.getAttribute("data-id");
            if (!confirm("Are you sure you want to delete this order from the database?")) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            try {
                const response = await fetch(`/admin/orders/${id}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken || "",
                        "Accept": "application/json",
                    },
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    const row = document.getElementById(`order_row_${id}`);
                    if (row) row.remove();
                    showToast("Order deleted from PostgreSQL database!");
                } else {
                    alert(data.message || "Failed to delete order.");
                }
            } catch (err) {
                console.error("Delete Error:", err);
                alert("Error deleting order from PostgreSQL database.");
            }
        }
    });

    // -------------------------------------------------------------
    // DYNAMIC FILTER BUTTONS
    // -------------------------------------------------------------
    filterBtns.forEach((btn) => {
        btn.addEventListener("click", () => {
            filterBtns.forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");

            const filter = btn.getAttribute("data-filter");
            const rows = tableBody ? tableBody.querySelectorAll("tr") : [];

            rows.forEach((row) => {
                const rowStatus = row.getAttribute("data-status");
                if (filter === "all" || rowStatus === filter) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    });
});
