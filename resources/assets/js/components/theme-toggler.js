export default function toggleTheme() {
    document
        .querySelector(".theme-toggler")
        .addEventListener("click", function () {
            {
                document.documentElement.classList.add("no-transition");
                document.documentElement.classList.toggle("dark");

                const isDark =
                    document.documentElement.classList.contains("dark");

                localStorage.setItem("theme", isDark ? "dark" : "light");

                setTimeout(() => {
                    document.documentElement.classList.remove("no-transition");
                }, 100);
            }
        });
}
