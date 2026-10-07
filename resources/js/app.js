

import Alpine from 'alpinejs';

const getPreferredTheme = () => {
    const storedTheme = localStorage.getItem('ducque-theme');
    if (storedTheme) return storedTheme;

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const setTheme = theme => {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem('ducque-theme', theme);
};

document.documentElement.dataset.theme = getPreferredTheme();

document.querySelectorAll('[data-theme-toggle]').forEach(button => {
    button.addEventListener('click', () => {
        setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
    });
});

window.Alpine = Alpine;

Alpine.start();
