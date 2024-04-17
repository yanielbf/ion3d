/**
 * This will track all the images and fonts for publishing.
 */
import.meta.glob(["../images/**", "../fonts/**"]);

/**
 * Main vue bundler.
 */
import { createApp } from "vue/dist/vue.esm-bundler";

/**
 * Main root application registry.
 */
window.app = createApp({
    data() {
        return {};
    },

    mounted() {
        this.lazyImages();

        this.animateBoxes();
    },

    methods: {
        onSubmit() {},

        onInvalidSubmit() {},

        lazyImages() {
            var lazyImages = [].slice.call(document.querySelectorAll('img.lazy'));

            let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
    
                        lazyImage.src = lazyImage.dataset.src;
                        
                        lazyImage.classList.remove('lazy');
    
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });
    
            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        },

        animateBoxes() {
            let animateBoxes = document.querySelectorAll('.scroll-trigger');

            if (! animateBoxes.length) {
                return;
            }

            animateBoxes.forEach((animateBox) => {
                let animateBoxObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            animateBox.classList.remove('scroll-trigger--offscreen');

                            animateBoxObserver.unobserve(animateBox);
                        }
                    });
                });
        
                animateBoxObserver.observe(animateBox);
            });
        }
    },
});

/**
 * Global plugins registration.
 */
import Axios from "./plugins/axios";
import Emitter from "./plugins/emitter";
import Shop from "./plugins/shop";
import VeeValidate from "./plugins/vee-validate";
import Flatpickr from "./plugins/flatpickr";
import Tres from '@tresjs/core';
import VueKonva from 'vue-konva';
import PrimeVue from 'primevue/config';
import Lara from './presets/lara';
import Designer3D from './components/Designer3D.vue';
import { OhVueIcon, addIcons } from "oh-vue-icons";
import { CoFacebookF, CoInstagram, CoTiktok, CoTelegram, CoYoutube } from "oh-vue-icons/icons/co";
import 'primeicons/primeicons.css'

addIcons(CoFacebookF, CoInstagram, CoTiktok, CoTelegram, CoYoutube);

[
    Axios,
    Emitter, 
    Shop, 
    VeeValidate, 
    Flatpickr,
    Tres,
    VueKonva
].forEach((plugin) => app.use(plugin));

app.use(PrimeVue, { unstyled: true, pt: Lara });

app.component('v-designer-3d', Designer3D);
app.component("v-icon", OhVueIcon);

/**
 * Load event, the purpose of using the event is to mount the application
 * after all of our `Vue` components which is present in blade file have
 * been registered in the app. No matter what `app.mount()` should be
 * called in the last.
 */
window.addEventListener("load", function (event) {
    app.mount("#app");
});
