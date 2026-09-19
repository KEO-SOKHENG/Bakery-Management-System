document.addEventListener("DOMContentLoaded", () => {
    const viewModal = document.getElementById("view_order_modal");
    const printModal = document.getElementById("print_receipt_modal");
    const closeViewBtn = document.getElementById("btn_close_view_modal");
    const closePrintBtn = document.getElementById("btn_close_print_modal");
    const triggerPrintBtn = document.getElementById("btn_trigger_print");

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove("active");
        document.body.style.overflow = "";
    }

    if (closeViewBtn) closeViewBtn.addEventListener("click", () => closeModal(viewModal));
    if (closePrintBtn) closePrintBtn.addEventListener("click", () => closeModal(printModal));

    [viewModal, printModal].forEach((m) => {
        if (m) {
            m.addEventListener("click", (e) => {
                if (e.target === m) closeModal(m);
            });
        }
    });

    if (triggerPrintBtn) {
        triggerPrintBtn.addEventListener("click", () => {
            window.print();
        });
    }

    // -------------------------------------------------------------
    // GLOBAL EVENT DELEGATION FOR VIEW & PRINT ORDER BUTTONS
    // -------------------------------------------------------------
    document.addEventListener("click", (e) => {
        // VIEW ORDER DETAILS
        const viewBtn = e.target.closest(".btn-view-order");
        if (viewBtn) {
            e.preventDefault();
            const rawData = viewBtn.getAttribute("data-order");
            if (!rawData) return;
            try {
                const order = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;

                document.getElementById("view_order_id").textContent = order.order_number || `#ORD-${order.id}`;
                document.getElementById("view_order_customer").textContent = order.customer_name || "Walk-in Customer";
                document.getElementById("view_order_type").textContent = order.order_type || "Store POS";
                document.getElementById("view_order_items").textContent = order.items_summary || "Assorted Bakery Items";
                document.getElementById("view_order_total").textContent = `$${parseFloat(order.total_amount || 0).toFixed(2)}`;
                document.getElementById("view_order_status").textContent = (order.status || "COMPLETED").toUpperCase();

                if (viewModal) {
                    if (viewModal.parentElement !== document.body) {
                        document.body.appendChild(viewModal);
                    }
                    viewModal.classList.add("active");
                    document.body.style.overflow = "hidden";
                }
            } catch (err) {
                console.error("View Order Parse Error:", err);
            }
        }

        // PRINT RECEIPT PREVIEW
        const printBtn = e.target.closest(".btn-print-receipt");
        if (printBtn) {
            e.preventDefault();
            const rawData = printBtn.getAttribute("data-order");
            if (!rawData) return;
            try {
                const order = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;

                document.getElementById("receipt_order_code").textContent = order.order_number || `#ORD-${order.id}`;
                document.getElementById("receipt_customer_name").textContent = order.customer_name || "Walk-in Customer";
                document.getElementById("receipt_order_type").textContent = order.order_type || "Store POS";
                document.getElementById("receipt_date_time").textContent = order.created_at ? new Date(order.created_at).toLocaleString() : "Today, 10:30 AM";
                document.getElementById("receipt_items_summary").textContent = order.items_summary || "Assorted Bakery Items";
                document.getElementById("receipt_total_amount").textContent = `$${parseFloat(order.total_amount || 0).toFixed(2)}`;

                if (printModal) {
                    printModal.classList.add("active");
                    document.body.style.overflow = "hidden";
                }
            } catch (err) {
                console.error("Print Receipt Parse Error:", err);
            }
        }
    });
});
