function loadPage(page){
    fetch(page + ".php")
        .then(res => {
            if (!res.ok) throw new Error("Not Found");
            return res.text();
        })
        .then(html => {
            document.getElementById("content").innerHTML = html;
        })
        .catch(() =>{
            document.getElementById("content").innerHTML =
                "<p>Page not found</p>";
        });
}

document.addEventListener("DOMContentLoaded", () => {
    loadPage("dashboard/dashbord");
});
document.addEventListener("submit", function(e){
    if (e.target && e.target.id === "addForm") {
        e.preventDefault();

        fetch(e.target.action, {
            method: "POST",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            body: new FormData(e.target)
        })
        .then(res => res.text())
        .then(msg => {
    const box = document.getElementById("formMessage");
    if (!box) return;

    box.innerHTML = msg;
    box.style.display = "block";

    if (msg.toLowerCase().includes("success")) {
        e.target.reset();
    }
})
        .catch(() => {
            document.getElementById("formMessage").innerHTML = "Server error";
        });
    }
});