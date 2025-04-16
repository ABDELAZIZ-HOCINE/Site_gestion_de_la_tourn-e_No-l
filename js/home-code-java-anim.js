// Fonction pour creer une etoile
function createStar() {
    const star = document.createElement('div');
    star.classList.add('star');
    star.style.width = Math.random() * 6 + 'px';
    star.style.height = star.style.width;
    star.style.left = Math.random() * 100 + 'vw';
    star.style.top = Math.random() * 100 + 'vh';
    document.querySelector('.stars-container').appendChild(star);
}

// creer plusieurs etoiles
for (let i = 0; i < 200; i++) {
    createStar();
}

const stars = document.querySelectorAll('.star');

stars.forEach(star =>   {
                            const duration = Math.random() * 7+2;
                            star.style.animation = `twinkle ${duration}s linear infinite alternate`;
                        });


//-------------------------------------------------------------------------------------------------------
document.getElementById('festiveButton').addEventListener('click', function() {
    console.log('Bouton cliqué !');

    const magicMessage = document.getElementById('magicMessage');
    console.log(magicMessage);

    if (magicMessage) {
        magicMessage.classList.add('show');
        console.log('Message affiché !');

        setTimeout(() => {
            magicMessage.classList.remove('show');
            console.log('Message caché !');
        }, 3000);
    } else {
        console.error('Element magicMessage non trouvé !');
    }
});