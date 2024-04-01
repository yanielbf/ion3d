<script setup>
import {
    inject,
    onMounted,
    defineProps,
    toRaw,
    watch,
    reactive,
    ref,
} from "vue";
import Dropdown from "primevue/dropdown";
import ProgressSpinner from "primevue/progressspinner";
import InputText from "primevue/inputtext";
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
    materials: [],
    includeText: false,
    activeSetting: 0,
    view2DLoaded: false,
    view: "3D",
    text: "",
    textSize: 20,
});

const container = ref();
const stage = ref();
const layer = ref();
const transformer = ref();
const stageConfig = ref({});
const textConfig = ref({});
let selectedShapeName = "";

// General

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

function handleChangeSettingView() {
    if (state.activeSetting == 0) {
        state.activeSetting = 1;
        state.view = "2D";
        if (!state.view2DLoaded) {
            state.view2DLoaded = true;
            init2d();
        }
    } else {
        state.activeSetting = 0;
        state.view = "3D";
    }
}

// 3D

function handleChangeColor(place, item) {
    if (place == 1) {
        state.borderColorSelected = item;
    } else {
        state.backColorSelected = item;
    }

    if (state.loading3D) return;

    scene.remove(scene.children[0]);
    const group = new THREE.Group();

    Object.keys(state.pieces).forEach((key, index) => {
        if (place == index) {
            let material = Object.values(state.materials)[index].clone();
            material.color.set(item.color);
            state.pieces[key].material = material;
        }
        group.add(toRaw(state.pieces[key]));
    });

    scene.add(group);
}

async function init3d() {
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
        state.materials = materials;

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

// 2D
function handleStageMouseDown(e) {
    if (e.target === e.target.getStage()) {
        selectedShapeName = "";
        handleUpdateTransformer();
        return;
    }

    const clickedOnTransformer =
        e.target.getParent().className === "Transformer";
    if (clickedOnTransformer) {
        return;
    }

    const name = e.target.name();
    selectedShapeName = name;
    handleUpdateTransformer();
}

function handleTransformEnd(e) {
    textConfig.value.x = e.target.x();
    textConfig.value.y = e.target.y();
    textConfig.value.rotation = e.target.rotation();
    textConfig.value.scaleX = e.target.scaleX();
    textConfig.value.scaleY = e.target.scaleY();
}

function handleTransformMoved(e) {
    const transformerNode = transformer.value.getNode();
    const stage = transformerNode.getStage();
    const boxes = transformerNode.nodes().map((node) => node.getClientRect());
    const box = getTotalBox(boxes);
    transformerNode.nodes().forEach((shape) => {
        const absPos = shape.getAbsolutePosition();
        // where are shapes inside bounding box of all shapes?
        const offsetX = box.x - absPos.x;
        const offsetY = box.y - absPos.y;

        // we total box goes outside of viewport, we need to move absolute position of shape
        const newAbsPos = { ...absPos };
        if (box.x < 0) {
            newAbsPos.x = -offsetX;
        }
        if (box.y < 0) {
            newAbsPos.y = -offsetY;
        }
        if (box.x + box.width > stage.width()) {
            newAbsPos.x = stage.width() - box.width - offsetX;
        }
        if (box.y + box.height > stage.height()) {
            newAbsPos.y = stage.height() - box.height - offsetY;
        }
        shape.setAbsolutePosition(newAbsPos);
    });
}

function handleDragEnd(e) {
    const textNode = e.target;
    var distanciaIzquierda = textNode.x();
    var distanciaDerecha =
        stageConfig.value.width - (textNode.x() + textNode.width());
    var distanciaArriba = textNode.y();
    var distanciaAbajo =
        stageConfig.value.height - (textNode.y() + textNode.height());
}

function handleUpdateTransformer() {
    const transformerNode = transformer.value.getNode();
    const stage = transformerNode.getStage();
    const selectedNode = stage.findOne("." + selectedShapeName);
    if (selectedNode === transformerNode.node()) {
        return;
    }
    if (selectedNode) {
        transformerNode.nodes([selectedNode]);
        const boxes = transformerNode
            .nodes()
            .map((node) => node.getClientRect());
        const box = getTotalBox(boxes);
        transformerNode.nodes().forEach((shape) => {
            const absPos = shape.getAbsolutePosition();
            const offsetX = box.x - absPos.x;
            const offsetY = box.y - absPos.y;

            const newAbsPos = { ...absPos };
            if (box.x < 0) {
                newAbsPos.x = -offsetX;
            }
            if (box.y < 0) {
                newAbsPos.y = -offsetY;
            }
            if (box.x + box.width > stage.width()) {
                newAbsPos.x = stage.width() - box.width - offsetX;
            }
            if (box.y + box.height > stage.height()) {
                newAbsPos.y = stage.height() - box.height - offsetY;
            }
            shape.setAbsolutePosition(newAbsPos);
        });
    } else {
        transformerNode.nodes([]);
    }
}

function getTotalBox(boxes) {
    let minX = Infinity;
    let minY = Infinity;
    let maxX = -Infinity;
    let maxY = -Infinity;

    boxes.forEach((box) => {
        minX = Math.min(minX, box.x);
        minY = Math.min(minY, box.y);
        maxX = Math.max(maxX, box.x + box.width);
        maxY = Math.max(maxY, box.y + box.height);
    });
    return {
        x: minX,
        y: minY,
        width: maxX - minX,
        height: maxY - minY,
    };
}

function getClientRect(rotatedBox) {
    const { x, y, width, height } = rotatedBox;
    const rad = rotatedBox.rotation;

    const p1 = getCorner(x, y, 0, 0, rad);
    const p2 = getCorner(x, y, width, 0, rad);
    const p3 = getCorner(x, y, width, height, rad);
    const p4 = getCorner(x, y, 0, height, rad);

    const minX = Math.min(p1.x, p2.x, p3.x, p4.x);
    const minY = Math.min(p1.y, p2.y, p3.y, p4.y);
    const maxX = Math.max(p1.x, p2.x, p3.x, p4.x);
    const maxY = Math.max(p1.y, p2.y, p3.y, p4.y);

    return {
        x: minX,
        y: minY,
        width: maxX - minX,
        height: maxY - minY,
    };
}

function getCorner(pivotX, pivotY, diffX, diffY, angle) {
    const distance = Math.sqrt(diffX * diffX + diffY * diffY);

    /// find angle from pivot to corner
    angle += Math.atan2(diffY, diffX);

    /// get new x and y and round it off to integer
    const x = pivotX + distance * Math.cos(angle);
    const y = pivotY + distance * Math.sin(angle);

    return { x: x, y: y };
}

function boundBoxFunc(oldBox, newBox) {
    const stageV = stage.value.getStage();
    const box = getClientRect(newBox);
    const isOut =
        box.x < 0 ||
        box.y < 0 ||
        box.x + box.width > stageV.width() ||
        box.y + box.height > stageV.height();

    if (isOut) {
        return oldBox;
    }
    return newBox;
}

function handleText(e) {
    if (state.text.length > 40) {
        state.text = state.text.slice(0, 40);
    } else {
        textConfig.value.text = e.target.value;
    }
}

function handleChangeSize(size) {
    state.textSize = size;
    textConfig.value.fontSize = size;
}

function init2d() {
    setTimeout(() => {
        stageConfig.value = {
            width: container.value.clientWidth,
            height: container.value.clientHeight,
        };
        console.log(stageConfig.value);
        const anchoCelda = stageConfig.value.width / 6;
        const altoCelda = stageConfig.value.height / 6;
        const numColumnas = 6;
        const numFilas = 6;

        for (var i = 0; i <= numColumnas; i++) {
            var x = i * anchoCelda;
            var lineaVertical = new Konva.Line({
                points: [x, 0, x, stageConfig.value.height],
                stroke: "rgba(0, 0, 0, 0.2)",
                strokeWidth: 1,
            });
            layer.value.getNode().add(lineaVertical);
        }

        for (var j = 0; j <= numFilas; j++) {
            var y = j * altoCelda;
            var lineaHorizontal = new Konva.Line({
                points: [0, y, stageConfig.value.width, y],
                stroke: "rgba(0, 0, 0, 0.2)",
                strokeWidth: 1,
            });
            layer.value.getNode().add(lineaHorizontal);
        }

        textConfig.value = {
            text: "",
            fontSize: 20,
            x: 0,
            y: 0,
            draggable: true,
            name: "text",
            fill: "black",
        };
    }, 1000);
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
            <div v-if="state.product" class="grid grid-cols-1 gap-2">
                <div id="viewer" class="border rounded-lg">
                    <div class="p-3 border-b pt-5 flex justify-center">
                        <button
                            type="button"
                            class="text-white bg-slate-700 hover:bg-slate-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                        >
                            Agregar al carrito
                        </button>
                        <button
                            type="button"
                            class="text-white bg-slate-700 hover:bg-slate-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                        >
                            Ir a comprar
                        </button>
                    </div>
                    <div v-show="state.view == '3D'">
                        <div v-if="state.loading3D">Cargando modelo 3D</div>
                        <div v-else-if="!state.loading3D && state.error3D">
                            {{ state.error3D }}
                        </div>
                        <div v-else class="h-[500px] w-full rounded-md p-5">
                            <TresCanvas clear-color="#fff" preset="realistic">
                                <TresPerspectiveCamera
                                    :position="[3, 2, -210]"
                                />
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
                    <div v-show="state.view == '2D'">
                        <div
                            class="w-full h-[500px] p-5 flex justify-center items-center"
                        >
                            <div
                                :class="{
                                    [`border-[${state.borderColorSelected.color}]`]: true,
                                    [`bg-[${state.backColorSelected.color}]`]: true,
                                }"
                                class="md:w-[20%] h-full rounded-xl grid grid-rows-[30%_1fr] grid-cols-1 p-1 border-8"
                            >
                                <div class="flex justify-end">
                                    <div
                                        class="w-[40%] h-[75%] mt-3 rounded-lg border-white bg-white mr-3"
                                    ></div>
                                </div>
                                <div
                                    class="w-full rounded -pb-4"
                                    ref="container"
                                >
                                    <v-stage
                                        ref="stage"
                                        :config="stageConfig"
                                        @mousedown="handleStageMouseDown"
                                        @touchstart="handleStageMouseDown"
                                    >
                                        <v-layer ref="layer">
                                            <v-text
                                                ref="text"
                                                :config="textConfig"
                                                @dragend="handleDragEnd"
                                                @transformend="
                                                    handleTransformEnd
                                                "
                                            />
                                            <v-transformer
                                                :centeredScaling="true"
                                                :rotationSnaps="[
                                                    0, 90, 180, 270,
                                                ]"
                                                :resizeEnabled="false"
                                                :flipEnabled="false"
                                                :boundBoxFunc="boundBoxFunc"
                                                ref="transformer"
                                                @dragmove="handleTransformMoved"
                                            />
                                        </v-layer>
                                    </v-stage>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="px-6 py-6 border-t grid grid-cols-[1fr_4fr_1fr]"
                    >
                        <div class="flex justify-start items-center">
                            <div
                                @click="handleChangeSettingView"
                                class="cursor-pointer"
                            >
                                <span
                                    class="pi pi-arrow-circle-left hover:text-blue-500"
                                    style="font-size: 2rem"
                                ></span>
                            </div>
                        </div>
                        <div
                            v-show="state.activeSetting === 0"
                            class="flex flex-col justify-center items-center"
                        >
                            <ColorSelector
                                label="Parte trasera"
                                :colors="colors.back"
                                :place="1"
                                @change-color="handleChangeColor"
                            />
                            <div class="h-5"></div>
                            <ColorSelector
                                label="Borde"
                                :colors="colors.side"
                                :place="0"
                                @change-color="handleChangeColor"
                            />
                        </div>
                        <div
                            v-show="state.activeSetting === 1"
                            class="flex flex-col items-center justify-center"
                        >
                            <div class="flex flex-col gap-2 w-1/2 mb-5">
                                <label>Texto</label>
                                <InputText
                                    v-model="state.text"
                                    @input="handleText"
                                />
                            </div>
                            <div class="mb-5 items-center gap-6 w-1/2">
                                <div class="mb-2">Tamaño</div>
                                <div class="flex flex-wrap gap-3">
                                    <div
                                        v-for="item in [20, 25, 30]"
                                        :key="item"
                                        :class="{
                                            'bg-red-400 text-white':
                                                item === state.textSize,
                                        }"
                                        class="cursor-pointer rounded-xl px-2 py-1 flex justify-center items-center bg-gray-300"
                                        @click="handleChangeSize(item)"
                                    >
                                        {{ fontSize[item] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end items-center">
                            <div
                                @click="handleChangeSettingView"
                                class="cursor-pointer"
                            >
                                <span
                                    class="pi pi-arrow-circle-right hover:text-blue-500"
                                    style="font-size: 2rem"
                                ></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="w-full flex flex-col items-center justify-center h-[500px]"
            >
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
