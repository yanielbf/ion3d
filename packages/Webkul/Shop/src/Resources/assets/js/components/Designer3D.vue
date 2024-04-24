<script setup>
import { inject, onMounted, toRaw, watch, reactive, ref, computed } from "vue";
import Dropdown from "primevue/dropdown";
import InputText from "primevue/inputtext";
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import * as THREE from "three";
import { TresCanvas } from "@tresjs/core";
import { useGLTF, OrbitControls } from "@tresjs/cientos";
import html2canvas from "html2canvas";
import ColorSelector from "./ColorSelector.vue";
import EmptyState from "./EmptyState.vue";

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
    visible: false,
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

function handleSelectFamily(family) {
    state.selectedFamilyAttribute = family;
    state.visible = false;
}

function handleGetFamiliesAttributes() {
    axios
        .get(`${props.info.urls.get_families_attributes}?code=${props.info.attributeFamily3d}`)
        .then((response) => {
            state.familyAttributes = response.data.data;
            if(props.info.attributeFamily3d) {
                state.selectedFamilyAttribute = state.familyAttributes.length && state.familyAttributes[0];
            } else {
                state.visible = true;
            }
        })
        .catch((error) => {
            state.error = error;
        });
}

function handleGetAttributes() {
    if (state.selectedFamilyAttribute) {
        state.loading = true;
        axios
            .get(
                `${props.info.urls.get_attributes_by_family}?familyId=${state.selectedFamilyAttribute.id}`
            )
            .then((response) => {
                state.attributes = response.data.data;
            })
            .catch((error) => {
                state.error = error;
            })
            .finally(() => {
                state.loading = false;
            });
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
            (x) => (query[x] = state.selectedAttributes[x])
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
    () => state.attributes,
    () => {
        if(Object.keys(props.info.attributes3d).length) {
            state.selectedAttributes = props.info.attributes3d;
        } else {
            state.selectedAttributes = state.attributes.reduce(
                (acc, item) => {
                    acc[item.code] = null;
                    return acc;
                },
                {}
            );
        }
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
            <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-indigo-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
            </svg>
        </div>
        <div
            v-else-if="state.error"
            class="w-full flex flex-col items-center justify-center h-[522px]"
        >
            <EmptyState />
            <div class="mt-2 text-gray-600 text-center">{{ state.error }}</div>
        </div>
        <div v-else>
            {{info.texts.phone_model}}
            <div class="grid md:grid-cols-2 grid-cols-1 gap-2 mb-7">
                <div v-for="attribute in state.attributes" :key="attribute.id">
                    <label class="text-gray-600 text-sm">{{ attribute.name }}</label>
                    <Dropdown
                        v-model="state.selectedAttributes[attribute.code]"
                        :filter="true"
                        :options="attribute.options"
                        optionLabel="name"
                        optionValue="id"
                        :placeholder="`${info.texts.select_attribute} ${attribute.name}`"
                        class="w-full"
                        showClear
                    />
                </div>
            </div>
            <div
                v-if="state.loadingProduct"
                class="flex justify-center items-center h-[616px]"
            >
                <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-indigo-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
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
                                {{ Object.values(state.selectedAttributes).reduce((acc, x) => `${acc} ${state.attributes.find(y => y.id == x)?.name || ''}`, '') }}
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
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <button
                                    @click="handleChangeQuantity('-')"
                                    class="mb-2 text-white bg-gray-700 hover:bg-indigo-500 rounded-full transition-all duration-700 text-sm px-5  py-3 focus:outline-none flex gap-2 items-center justify-center"
                                >
                                    -
                                </button>
                                <InputText
                                    v-model.number="state.quantity"
                                    class="mb-2"
                                />
                                <button
                                    @click="handleChangeQuantity('+')"
                                    class="mb-2 text-white bg-gray-700 hover:bg-indigo-500 rounded-full transition-all duration-700 text-sm px-5 py-3 focus:outline-none flex gap-2 items-center justify-center"
                                >
                                    +
                                </button>
                            </div>
                            <div class="text-lg font-medium">
                                {{
                                    `${
                                        state.product.prices.final.price *
                                        state.quantity
                                    } ${info.currency}`
                                }}
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
                            <button
                                @click="handleRestart"
                                class="text-white bg-gray-700 hover:bg-indigo-500 rounded-full transition-all duration-700 text-sm px-5 py-3 focus:outline-none flex gap-2 items-center justify-center"
                            >
                                <span>{{info.texts.restart_values}}</span>
                            </button>
                            <button
                                @click="handleAddtoCart(false)"
                                class="text-white bg-gray-700 hover:bg-indigo-500 rounded-full transition-all duration-700 text-sm px-5 py-3 focus:outline-none flex gap-2 items-center justify-center"
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
                                class="text-white bg-gray-700 hover:bg-indigo-500 rounded-full transition-all duration-700 text-sm px-5 py-3 focus:outline-none flex gap-2 items-center justify-center"
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
                <EmptyState />
                <div class="mt-2 text-gray-600 text-center mb-1">{{info.texts.custom_your_cover}}</div>
                <div v-if="state.familyAttributes.length > 1" class="mt-2 text-gray-600 text-center mb-1">{{info.texts.or}}</div>
                <div @click="state.visible = true" v-if="state.familyAttributes.length > 1" class="mt-2 py-2 px-4 bg-gray-700 hover:bg-indigo-800 transition-all duration-700 rounded-full shadow-xs text-white text-sm text-center cursor-pointer max-sm:px-5">{{info.texts.change_family}}</div>
            </div>
        </div>
    </div>
    <Dialog v-model:visible="state.visible" :closable="false" :closeOnEscape="false" modal header="Selecciona un tipo de producto" :style="{ width: '25rem' }">
        <div @click="handleSelectFamily(family)" class="border py-2 px-2 rounded-full mb-2 cursor-pointer bg-gray-700 hover:bg-indigo-500 text-white text-center transition-all duration-700" v-for="family in state.familyAttributes" :key="family.code">
            {{ family.name}}
        </div>
    </Dialog>
</template>
