<script setup>
import { inject, onMounted, defineProps, toRaw, watch, reactive } from "vue";
import Dropdown from "primevue/dropdown";
import ProgressSpinner from "primevue/progressspinner";
import InputSwitch from "primevue/inputswitch";
import Checkbox from "primevue/checkbox";
import * as THREE from "three";
import { TresCanvas } from "@tresjs/core";
import { useGLTF, OrbitControls } from "@tresjs/cientos";
import ColorSelector from "./ColorSelector.vue";

const colors = {
    side: [
        { id: 1, color: "#057EB5" },
        { id: 2, color: "#00138D" },
        { id: 3, color: "#8A0156" },
        { id: 4, color: "#EB6B2B" },
        { id: 5, color: "#232325" },
        { id: 6, color: "#8A33EE" },
        { id: 7, color: "#FA1432" },
        { id: 8, color: "#305E9B" },
    ],
    back: [
        { id: 1, color: "#83BE01" },
        { id: 2, color: "#DEB229" },
        { id: 3, color: "#A8A8A8" },
        { id: 4, color: "#F0F0F2" },
        { id: 5, color: "#7DFC01" },
        { id: 6, color: "#FFFD03" },
        { id: 7, color: "#FEC708" },
        { id: 8, color: "#563E3B" },
    ],
};

const fontSize = {
    20: "Normal",
    25: "Mediano",
    30: "Grande",
};

let scene = new THREE.Scene();

const axios = inject("$axios");

const props = defineProps({
    info: {
        type: Object,
    },
});

const state = reactive({
    loading: true,
    loadingProduct: false,
    loading3D: true,
    error: null,
    errorProduct: null,
    error3D: null,
    familyAttributes: [],
    selectedFamilyAttribute: null,
    attributes: [],
    selectedAttributes: {},
    product: null,
    backColorSelected: colors.back[0],
    borderColorSelected: colors.side[0],
    pieces: [],
    includeText: false,
});

function handleGetFamiliesAttributes() {
    axios
        .get(props.info.urls.get_families_attributes)
        .then((response) => {
            state.familyAttributes = response.data.data;
            state.selectedFamilyAttribute =
                state.familyAttributes.length && state.familyAttributes[0];
        })
        .catch((error) => {
            state.error = error;
        });
}

function handleGetAttributes() {
    if (state.selectedFamilyAttribute) {
        axios
            .get(
                `${props.info.urls.get_attributes_by_family}?familyId=${state.selectedFamilyAttribute.id}`
            )
            .then((response) => {
                state.attributes = response.data.data;
                state.selectedAttributes = state.attributes.reduce(
                    (acc, item) => {
                        acc[item.code] = null;
                        return acc;
                    },
                    {}
                );
            })
            .catch((error) => {
                state.error = error;
            })
            .finally(() => {
                state.loading = false;
            });
    } else {
    }
}

function handleGetProduct() {
    if (
        Object.keys(state.selectedAttributes).every(
            (x) => state.selectedAttributes[x]
        )
    ) {
        state.loadingProduct = true;
        const query = {};
        Object.keys(state.selectedAttributes).forEach(
            (x) => (query[x] = state.selectedAttributes[x].id)
        );
        const url =
            Object.keys(query) != ""
                ? `${
                      props.info.urls.get_product_by_attributes
                  }?${new URLSearchParams(query).toString()}`
                : props.info.urls.get_product_by_attributes;
        axios
            .get(url)
            .then((response) => {
                state.product = response.data?.data;
            })
            .catch((error) => {
                state.errorProduct = error;
            })
            .finally(() => {
                state.loadingProduct = false;
            });
    } else {
        state.product = null;
    }
}

function handleChangeColor(place, item) {
    if (place == 1) {
        borderColorSelected.value = item;
    } else {
        backColorSelected.value = item;
    }

    if (state.loading3D) return;

    scene = new THREE.Scene();
    const group = new THREE.Group();

    let pieceSelected = Object.values(state.pieces).find(
        (item, index) => index === place
    );
    console.log(pieceSelected);

    // const pieceSelected = pieces.value.filter((x) => x.key === piece)[0];
    // const pieceToChange = toRaw(pieceSelected.value);
    // let mat = toRaw(pieceSelected.material);
    // mat.color.set(item.color);
    // pieceToChange.material = mat;
    // group.add(pieceToChange);

    // for (const iterator of pieces.value) {
    //     if (iterator.key !== piece) {
    //         group.add(toRaw(iterator.value));
    //     }
    // }

    // scene.add(group);
}

async function init3d() {
    console.log(state.product);
    try {
        state.error3D = null;
        state.loading3D = true;

        scene = new THREE.Scene();
        const group = new THREE.Group();

        const { nodes, materials } = await useGLTF(state.product.design3d, {
            draco: true,
        });

        const keys = Object.keys(nodes).filter(
            (key) => ![1, 2].includes(key.toString().length) && key !== "Scene"
        );
        state.pieces = Object.fromEntries(keys.map((key) => [key, nodes[key]]));

        if (
            Object.values(state.pieces).length !== 2 &&
            Object.values(materials).length !== 2
        ) {
            console.log(state.pieces);
            console.log(materials);
            throw new Exception(
                "El objecto glb no esta en el formato esperado"
            );
        }

        Object.keys(state.pieces).forEach((key, index) => {
            const material = Object.values(materials)[index];
            if (index == 0) {
                material.color.set(state.borderColorSelected.color);
            } else {
                material.color.set(state.backColorSelected.color);
            }
            state.pieces[key].material = material;
            group.add(toRaw(state.pieces[key]));
        });

        scene.add(group);
    } catch (error) {
        console.log(error);
        state.error3D = "No se pudo cargar el modelo 3D";
    } finally {
        state.loading3D = false;
    }
}

watch(
    () => state.selectedFamilyAttribute,
    () => {
        handleGetAttributes();
    },
    {
        deep: true,
    }
);

watch(
    () => state.selectedAttributes,
    () => {
        handleGetProduct();
    },
    {
        deep: true,
    }
);

watch(
    () => state.product,
    () => {
        if (state.product) {
            init3d();
        }
    },
    {
        deep: true,
    }
);

onMounted(() => {
    handleGetFamiliesAttributes();
});
</script>

<template>
    <div class="container mt-8 px-[60px] max-lg:px-8">
        <div v-if="state.loading" class="flex justify-center">
            <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="4"
                class="fill-surface-0 dark:fill-surface-800"
                animationDuration=".5s"
                aria-label="Custom ProgressSpinner"
            />
        </div>
        <div v-else>
            <div class="grid md:grid-cols-2 grid-cols-1 gap-2 mb-7">
                <div v-for="attribute in state.attributes" :key="attribute.id">
                    <label>{{ attribute.name }}</label>
                    <Dropdown
                        v-model="state.selectedAttributes[attribute.code]"
                        :filter="true"
                        :options="attribute.options"
                        optionLabel="name"
                        :placeholder="`Select a ${attribute.name}`"
                        class="w-full"
                    />
                </div>
            </div>
            <div v-if="state.product" class="grid grid-cols-2 gap-2">
                <div id="viewer" class="border rounded-md">
                    <div>
                        <div v-if="state.loading3D">Cargando modelo 3D</div>
                        <div v-else-if="!state.loading3D && state.error3D">
                            {{ state.error3D }}
                        </div>
                        <div v-else class="h-[500px] w-full rounded-md p-5">
                            <TresCanvas clear-color="#fff" preset="realistic">
                                <TresPerspectiveCamera :position="[3, 2, -210]" />
                                <OrbitControls :enableZoom="false" />
                                <Suspense>
                                    <primitive :object="scene" />
                                </Suspense>
                                <TresDirectionalLight
                                    :intensity="2"
                                    :position="[3, 3, 3]"
                                />
                                <TresAmbientLight :intensity="1" />
                            </TresCanvas>
                        </div>
                    </div>
                </div>
                <div id="settings" class="border rounded-md px-8 py-5">
                    <div class="mb-4 font-semibold text-xl">
                        Opciones de personalización
                    </div>
                    <ColorSelector
                        label="Parte trasera"
                        :colors="colors.back"
                        :place="1"
                        @change-color="handleChangeColor"
                    />
                    <ColorSelector
                        label="Borde"
                        :colors="colors.side"
                        :place="2"
                        @change-color="handleChangeColor"
                    />
                    <div class="flex flex-col gap-2">
                        <div>Incluir texto</div>
                        <Checkbox v-model="state.includeText" :binary="true" />
                    </div>
                </div>
            </div>
            <div v-else class="w-full flex flex-col items-center justify-center h-[500px]">
                <div class="mb-5">Empieza a personalizar tu cover</div>
                <svg
                    class="h-[400px]"
                    fill="#000000"
                    version="1.1"
                    id="Capa_1"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                    width="800px"
                    height="800px"
                    viewBox="0 0 93.268 93.268"
                    xml:space="preserve"
                >
                    <g>
                        <g>
                            <path
                                d="M71.065,4.551V4.035C71.065,1.807,69.259,0,67.03,0H26.225c-2.229,0-4.035,1.807-4.035,4.035v1.474h0.018v82.25H22.19
			v1.474c0,2.229,1.807,4.035,4.035,4.035H67.03c2.229,0,4.035-1.807,4.035-4.035v-0.974h0.012V4.551H71.065z M47.197,12.953
			c0.412-0.484,1.11-0.845,1.688-0.869c0.074,0.673-0.195,1.347-0.597,1.832c-0.402,0.485-1.059,0.862-1.701,0.812
			C46.5,14.069,46.824,13.383,47.197,12.953z M35.806,3.862c0,1.453-1.178,2.63-2.63,2.63H27.85c-1.452,0-2.63-1.178-2.63-2.63
			V3.788c0-1.453,1.178-2.63,2.63-2.63h5.326c1.452,0,2.63,1.178,2.63,2.63V3.862z M50.479,22.131
			c-0.479,0.698-0.975,1.394-1.756,1.409c-0.771,0.014-1.016-0.456-1.896-0.456c-0.877,0-1.152,0.441-1.879,0.47
			c-0.754,0.028-1.328-0.754-1.811-1.452c-0.986-1.423-1.738-4.023-0.727-5.779c0.502-0.872,1.398-1.423,2.373-1.438
			c0.74-0.013,1.439,0.499,1.893,0.499s1.303-0.616,2.195-0.526c0.375,0.016,1.424,0.151,2.098,1.137
			c-0.053,0.035-1.252,0.731-1.237,2.184c0.017,1.735,1.521,2.313,1.541,2.321C51.261,20.54,51.031,21.322,50.479,22.131z"
                            />
                            <circle cx="28.266" cy="3.755" r="1.72" />
                            <circle cx="33.328" cy="3.755" r="1.168" />
                        </g>
                    </g>
                </svg>
            </div>
        </div>
    </div>
</template>
