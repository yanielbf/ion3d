<script setup>
import { inject, onMounted, toRaw, watch, reactive, ref, computed } from "vue";
import Dropdown from "primevue/dropdown";
import ProgressSpinner from "primevue/progressspinner";
import InputText from "primevue/inputtext";
import * as THREE from "three";
import { TresCanvas } from "@tresjs/core";
import { useGLTF, OrbitControls } from "@tresjs/cientos";
import html2canvas from "html2canvas";
import ColorSelector from "./ColorSelector.vue";

const props = defineProps({
    info: {
        type: Object,
    },
});

const colors = {
    side: props.info.coverSideColors,
    back: props.info.coverBackColors,
};

const fontSize = {
    20: props.info.texts.text_size_normal,
    25: props.info.texts.text_size_medium,
    30: props.info.texts.text_size_big,
};

let scene = new THREE.Scene();

const axios = inject("$axios");
const emitter = inject("$emitter");

const state = reactive({
    loading: true,
    loadingProduct: false,
    loading3D: true,
    loadingAddCart: false,
    loadingBuyNow: false,
    loadingShop: false,
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
    activeSetting: 0,
    view2DLoaded: false,
    view: "3D",
    text: "",
    textSize: 20,
    quantity: 1,
});

const screenShot = ref();
const container = ref();
const stage = ref();
const layer = ref();
const transformer = ref();
const stageConfig = ref({});
const textConfig = ref({});
let selectedShapeName = "";

const codeCover = computed(
    () =>
        `product_${state.product.id}_back_${
            state.backColorSelected.id
        }_${state.backColorSelected.color.replace("#", "")}_side_${
            state.borderColorSelected.id
        }_${state.borderColorSelected.color.replace("#", "")}_text_${
            state.text
        }_fontSize_${state.textSize}`
);

// General
function handleCreateHash() {
    let hash = 0;
    for (let i = 0; i < codeCover.value.length; i++) {
        hash += codeCover.value.charCodeAt(i);
    }
    hash += textConfig.value.x || 0;
    hash += textConfig.value.y || 0;
    hash += textConfig.value.rotation || 0;
    hash += textConfig.value.scaleX || 0;
    hash += textConfig.value.scaleY || 0;
    return hash.toString(16);
}

function handleGetFamiliesAttributes() {
    axios
        .get(`${props.info.urls.get_families_attributes}?type=${props.info.type}`)
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

function handleChangeQuantity(type) {
    if (type == "+") {
        state.quantity += 1;
    } else {
        state.quantity = state.quantity - 1 == 0 ? 1 : state.quantity - 1;
    }
}

function handleRestart() {
    if (state.loadingAddCart || state.loadingBuyNow) {
        return;
    }
    state.backColorSelected = colors.back[0];
    state.borderColorSelected = colors.side[0];
    state.activeSetting = 0;
    state.view2DLoaded = false;
    state.view = "3D";
    state.quantity = 1;
    state.text = "";
    state.textSize = 20;
    textConfig.value = {};
}

async function handleAddtoCart(buyNow) {
    if (state.loadingAddCart || state.loadingBuyNow) {
        return;
    }
    if (state.quantity < 1) {
        emitter.emit("add-flash", {
            type: "error",
            message: props.info.texts.validation_quantity,
        });
        return;
    }
    if (buyNow) {
        state.loadingBuyNow = true;
    } else {
        state.loadingAddCart = true;
    }
    setTimeout(() => {
        html2canvas(screenShot.value, {
            useCORS: true,
            allowTaint: true,
            onclone: function (clonedDoc) {
                clonedDoc.getElementById('view2D').style.display = 'block';
                clonedDoc.getElementById('model').style.display = 'block';
            }
        }).then(function (canvas) {
            canvas.toBlob(function (blob) {
                const url = `${props.info.urls.add_item_to_cart}${
                    buyNow ? "?is_buy_now=1" : ""
                }`;
                const hash = handleCreateHash();
                axios
                    .postForm(url, {
                        product_id: state.product.id,
                        quantity: state.quantity,
                        image: blob,
                        hash: hash,
                        design: {
                            [hash]: {
                                filename: `${codeCover.value}.png`,
                                quantity: state.quantity,
                            },
                        },
                    })
                    .then((response) => {
                        if (!response.data.data.message) {
                            emitter.emit(
                                "update-mini-cart",
                                response.data.data
                            );
                            emitter.emit("add-flash", {
                                type: "success",
                                message: response.data.message,
                            });
                            handleRestart();
                            if (buyNow) {
                                window.location = response.data.redirect;
                            }
                        } else {
                            emitter.emit("add-flash", {
                                type: "error",
                                message: response.data.data.message,
                            });
                        }
                    })
                    .finally(() => {
                        if (buyNow) {
                            state.loadingBuyNow = false;
                        } else {
                            state.loadingAddCart = false;
                        }
                    });
            }, "image/png");
        });
    }, 1000);
}

// 3D
function handleChangeColor(place, item) {
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
        const anchoCelda = stageConfig.value.width / 6;
        const altoCelda = stageConfig.value.height / 6;
        const numColumnas = 6;
        const numFilas = 6;

        for (var i = 0; i <= numColumnas; i++) {
            var x = i * anchoCelda;
            var lineaVertical = new Konva.Line({
                points: [x, 0, x, stageConfig.value.height],
                stroke: "rgba(0, 0, 0, 0.1)",
                strokeWidth: 1,
            });
            layer.value.getNode().add(lineaVertical);
        }

        for (var j = 0; j <= numFilas; j++) {
            var y = j * altoCelda;
            var lineaHorizontal = new Konva.Line({
                points: [0, y, stageConfig.value.width, y],
                stroke: "rgba(0, 0, 0, 0.1)",
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

watch(
    () => state.backColorSelected,
    () => {
        handleChangeColor(1, state.backColorSelected)
    },
    {
        deep: true,
    }
);

watch(
    () => state.borderColorSelected,
    () => {
        handleChangeColor(0, state.borderColorSelected)
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
    <div class="container mt-0 md:mt-2 px-[60px] max-lg:px-8">
        <div
            v-if="state.loading"
            class="flex justify-center items-center h-[616px]"
        >
            <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="4"
                class="fill-surface-0 dark:fill-surface-800"
                animationDuration=".5s"
                aria-label="Custom ProgressSpinner"
            />
        </div>
        <div v-else>
            {{info.texts.phone_model}}
            <div class="grid md:grid-cols-2 grid-cols-1 gap-2 mb-7">
                <div v-for="attribute in state.attributes" :key="attribute.id">
                    <label>{{ attribute.name }}</label>
                    <Dropdown
                        v-model="state.selectedAttributes[attribute.code]"
                        :filter="true"
                        :options="attribute.options"
                        optionLabel="name"
                        :placeholder="`${info.texts.select_attribute} ${attribute.name}`"
                        class="w-full"
                    />
                </div>
            </div>
            <div
                v-if="state.loadingProduct"
                class="flex justify-center items-center h-[616px]"
            >
                <ProgressSpinner
                    style="width: 50px; height: 50px"
                    strokeWidth="4"
                    class="fill-surface-0 dark:fill-surface-800"
                    animationDuration=".5s"
                    aria-label="Custom ProgressSpinner"
                />
            </div>
            <div v-if="!state.loadingProduct && state.product" class="grid grid-cols-1 gap-2">
                <div id="viewer" class="rounded-lg">
                    <div ref="screenShot">
                        <div
                            v-show="state.view == '3D'"
                            data-html2canvas-ignore="true"
                        >
                            <div
                                class="h-[500px] flex justify-center items-center"
                                v-if="state.loading3D"
                            >
                                {{info.texts.loading_modal_3d}}
                            </div>
                            <div class="h-[500px] flex justify-center items-center" v-else-if="!state.loading3D && state.error3D">
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
                        <div id="view2D" v-show="state.view == '2D'" class="pb-8">
                            <div
                                class="w-full h-[500px] pt-10 pb-5 flex justify-center items-center"
                            >
                                <div
                                    :class="{
                                        [`border-[${state.borderColorSelected.color}]`]: true,
                                        [`bg-[${state.backColorSelected.color}]`]: true,
                                    }"
                                    class="w-[70%] md:w-1/3 max-w-[240px] h-full rounded-xl grid grid-rows-[30%_1fr] grid-cols-1 p-1 border-8"
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
                            <div id="model" class="text-center" style="display:none;">
                                {{ Object.values(state.selectedAttributes).reduce((acc, x) => `${acc} ${x.name}`, '') }}
                            </div>
                        </div>
                        <div v-show="state.view == 'Images'" data-html2canvas-ignore="true">
                            <img :src="state.product.images[0].medium_image_url" />
                        </div>
                    </div>
                    <div class="py-6 border-t grid" :class="{'grid-cols-[1fr_4fr_1fr]': info.enableScreenText, 'grid-cols-1': !info.enableScreenText}">
                        <div class="flex justify-start items-center">
                            <div
                                @click="handleChangeSettingView"
                                class="cursor-pointer"
                                v-if="info.enableScreenText"
                            >
                                <span
                                    class="pi pi-arrow-circle-left text-slate-700 hover:text-slate-500"
                                    style="font-size: 2rem"
                                ></span>
                            </div>
                        </div>
                        <div
                            v-show="state.activeSetting === 0"
                            class="flex flex-col justify-center items-center"
                        >
                            <ColorSelector
                                :label="info.texts.back_piece"
                                :colors="colors.back"
                                v-model="state.backColorSelected"
                            />
                            <div class="h-5"></div>
                            <ColorSelector
                                :label="info.texts.side_piece"
                                :colors="colors.side"
                                v-model="state.borderColorSelected"
                            />
                        </div>
                        <div
                            v-show="state.activeSetting === 1"
                            class="flex flex-col items-center justify-center"
                        >
                            <div class="flex flex-col gap-2 mb-5 w-full md:w-1/2">
                                <label>{{info.texts.text_print}}</label>
                                <InputText
                                    class="w-full"
                                    v-model="state.text"
                                    @input="handleText"
                                />
                            </div>
                            <div class="mb-5 items-center gap-6 w-1/2">
                                <div class="mb-2">{{info.texts.text_size}}</div>
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
                                v-if="info.enableScreenText"
                            >
                                <span
                                    class="pi pi-arrow-circle-right text-slate-700 hover:text-slate-500"
                                    style="font-size: 2rem"
                                ></span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t py-6 grid grid-cols-1 md:grid-cols-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 place-items-center">
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    @click="handleChangeQuantity('-')"
                                    class="mb-2 text-white bg-slate-700 hover:bg-slate-800 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none"
                                >
                                    -
                                </button>
                                <InputText
                                    v-model.number="state.quantity"
                                    class="mb-2"
                                />
                                <button
                                    @click="handleChangeQuantity('+')"
                                    class="mb-2 text-white bg-slate-700 hover:bg-slate-800 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none"
                                >
                                    +
                                </button>
                            </div>
                            <div class="text-lg font-medium mb-3">
                                {{
                                    `${
                                        state.product.prices.final.price *
                                        state.quantity
                                    } ${info.currency}`
                                }}
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <button
                                @click="handleRestart"
                                class="text-white bg-slate-700 hover:bg-slate-800 rounded-lg text-sm px-3 py-2.5 focus:outline-none flex gap-2 items-center justify-center"
                            >
                                <span>{{info.texts.restart_values}}</span>
                            </button>
                            <button
                                @click="handleAddtoCart(false)"
                                class="text-white bg-slate-700 hover:bg-slate-800 rounded-lg text-sm px-5 py-2.5 focus:outline-none flex gap-2 items-center justify-center"
                            >
                                <i
                                    v-if="state.loadingAddCart"
                                    class="pi pi-spin pi-spinner"
                                    style="font-size: 1rem"
                                />
                                <span>{{info.texts.add_to_cart}}</span>
                            </button>
                            <button
                                @click="handleAddtoCart(true)"
                                class="text-white bg-slate-700 hover:bg-slate-800 rounded-lg text-sm px-5 py-2.5 focus:outline-none flex gap-2 items-center justify-center"
                            >
                                <i
                                    v-if="state.loadingBuyNow"
                                    class="pi pi-spin pi-spinner"
                                    style="font-size: 1rem"
                                />
                                <span>{{info.texts.add_to_cart_finish}}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-if="!state.loadingProduct && !state.product"
                class="w-full flex flex-col items-center justify-center h-[522px]"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                    version="1.1"
                    x="0px"
                    y="0px"
                    viewBox="0 0 512 540"
                    style="enable-background: new 0 0 512 512"
                    xml:space="preserve"
                    class="w-1/2 md:w-1/5"
                >
                    <g>
                        <path
                            d="M196.3,111.7c12.4,0,22.5-10.1,22.5-22.5s-10.1-22.5-22.5-22.5s-22.5,10.1-22.5,22.5S183.9,111.7,196.3,111.7z M196.3,81.7   c4.1,0,7.5,3.4,7.5,7.5s-3.4,7.5-7.5,7.5s-7.5-3.4-7.5-7.5S192.1,81.7,196.3,81.7z"
                        />
                        <path
                            d="M221.9,119.2c0,12.4,10.1,22.5,22.5,22.5s22.5-10.1,22.5-22.5s-10.1-22.5-22.5-22.5S221.9,106.8,221.9,119.2z M244.4,111.7   c4.1,0,7.5,3.4,7.5,7.5s-3.4,7.5-7.5,7.5s-7.5-3.4-7.5-7.5S240.3,111.7,244.4,111.7z"
                        />
                        <circle cx="244.4" cy="77.1" r="10.8" />
                        <circle cx="244.4" cy="156.7" r="7.5" />
                        <path
                            d="M196.3,171.7c12.4,0,22.5-10.1,22.5-22.5s-10.1-22.5-22.5-22.5s-22.5,10.1-22.5,22.5S183.9,171.7,196.3,171.7z    M196.3,141.7c4.1,0,7.5,3.4,7.5,7.5s-3.4,7.5-7.5,7.5s-7.5-3.4-7.5-7.5S192.1,141.7,196.3,141.7z"
                        />
                        <path
                            d="M256,248.5c-20,0-36.2,16.3-36.2,36.2S236,321,256,321s36.2-16.3,36.2-36.2S276,248.5,256,248.5z M256,306   c-11.7,0-21.2-9.5-21.2-21.2s9.5-21.2,21.2-21.2c11.7,0,21.2,9.5,21.2,21.2S267.7,306,256,306z"
                        />
                        <rect x="142.5" y="107.8" width="15" height="15" />
                        <path
                            d="M298.1,71.7c0-18.9-15.3-34.2-34.2-34.2h-87.2c-18.9,0-34.2,15.3-34.2,34.2v21.1h15V71.7c0-10.6,8.6-19.2,19.2-19.2h87.2   c10.6,0,19.2,8.6,19.2,19.2v87.2c0,10.6-8.6,19.2-19.2,19.2h-87.2c-10.6,0-19.2-8.6-19.2-19.2v-21.1h-15v21.1   c0,18.9,15.3,34.2,34.2,34.2h87.2c18.9,0,34.2-15.3,34.2-34.2V71.7z"
                        />
                        <path
                            d="M348.9,7.5H163.1c-27.9,0-50.6,22.7-50.6,50.6v395.8c0,27.9,22.7,50.6,50.6,50.6h185.7c27.9,0,50.6-22.7,50.6-50.6v-11.1   h-15v11.1c0,19.6-16,35.6-35.6,35.6H163.1c-19.6,0-35.6-16-35.6-35.6V58.1c0-19.6,16-35.6,35.6-35.6h185.7   c19.6,0,35.6,16,35.6,35.6v339.7h15V58.1C399.5,30.2,376.8,7.5,348.9,7.5z"
                        />
                        <rect x="384.5" y="412.8" width="15" height="15" />
                    </g>
                </svg>
                <div class="mt-2">{{info.texts.custom_your_cover}}</div>
            </div>
        </div>
    </div>
</template>
