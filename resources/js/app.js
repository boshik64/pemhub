document.addEventListener('notify', event => {
    const { type, message } = event.detail;

    // Используем библиотеку уведомлений или стандартное окно
    alert(`${type.toUpperCase()}: ${message}`);
});
