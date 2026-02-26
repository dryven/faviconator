import Settings from './pages/Settings.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('faviconator::settings', Settings);
});