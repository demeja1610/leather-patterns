export default function contextMenu() {
    const menus = document.querySelectorAll("[data-context-menu]");

    if (menus.length === 0) {
        return;
    }

    let activeMenu = null;

    document.addEventListener("click", (event) => {
        const target = event.target;

        // Ищем триггер: проверяем сам target или его родительские элементы
        const trigger = target.closest("[data-context-menu-trigger]");

        // 1. Проверяем, кликнули ли по кнопке вызова меню (или внутри неё)
        if (trigger) {
            // Теперь предотвращение стандартного поведения сработает в 100% случаев
            event.preventDefault();

            // Находим меню, к которому относится этот конкретный триггер
            const currentMenu = trigger.closest("[data-context-menu]");

            if (!currentMenu) return; // На случай, если разметка нарушена

            // Если кликнули по кнопке уже открытого меню — закрываем его
            if (activeMenu === currentMenu) {
                closeActiveMenu();
                return;
            }

            // Если открыто другое меню — сначала закрываем его
            closeActiveMenu();

            // Открываем новое меню
            currentMenu.classList.add("active");
            activeMenu = currentMenu;
            return;
        }

        // 2. Если кликнули ВНУТРИ открытого меню (например, по кнопке действия)
        if (activeMenu && activeMenu.contains(target)) {
            closeActiveMenu();
            return;
        }

        // 3. Во всех остальных случаях (клик мимо любого меню и кнопок)
        closeActiveMenu();
    });

    // Функция для закрытия вынесена в область видимости contextMenu
    function closeActiveMenu() {
        if (activeMenu) {
            activeMenu.classList.remove("active");
            activeMenu = null;
        }
    }
}
