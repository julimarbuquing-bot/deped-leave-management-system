body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f7fb;
}

.login-body {
    background: linear-gradient(135deg, #eff5ff, #ffffff);
}

.brand-badge {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: bold;
}

.card {
    border-radius: 16px;
}

.badge {
    font-size: 0.8rem;
}

.timeline {
    position: relative;
    margin-left: 12px;
    padding-left: 16px;
    border-left: 2px solid #dfe7f1;
}

.timeline-item {
    position: relative;
    margin-bottom: 16px;
}

.timeline-item::before {
    content: "";
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #0d6efd;
}

@media print {
    .navbar, .btn, .no-print {
        display: none !important;
    }
    body {
        background: white;
    }
}
