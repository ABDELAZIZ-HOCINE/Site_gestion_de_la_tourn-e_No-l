function setActiveMenuItem() {
    const currentPath = window.location.pathname.split('/').pop(); // Récupère uniquement le nom du fichier
    const menuItems = document.querySelectorAll('.elms_nav .menu-item');

    menuItems.forEach(item => {
        const itemPath = item.getAttribute('href').split('/').pop(); // Récupère uniquement le nom du fichier
        if (itemPath === currentPath) {
            item.classList.add('selected');
        } else {
            item.classList.remove('selected');
        }
    });
}

// Appeler la fonction après le chargement du DOM
document.addEventListener("DOMContentLoaded", function () {
    setActiveMenuItem();
});
//------------------------------------------------------------------------------------------------------------------
function toggleNav(){
    var navigation = document.querySelector('.nav2');
    navigation.classList.toggle('open');
}
//------------------------------------------------------------------------------------------------------------------

//---------------------------------------------------------------------------------------------------------------
