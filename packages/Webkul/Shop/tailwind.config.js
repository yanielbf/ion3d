/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: "selector",
    content: [
        "./src/Resources/**/*.blade.php",
        "./src/Resources/**/*.js",
        ,
        "./src/Resources/**/*.vue",
    ],
    theme: {
        container: {
            center: true,

            screens: {
                "2xl": "1440px",
            },

            padding: {
                DEFAULT: "90px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
            1180: "1180px",
            1060: "1060px",
            991: "991px",
            868: "868px",
        },

        extend: {
            colors: {
                navyBlue: "#060C3B",
                lightOrange: "#F6F2EB",
                darkGreen: "#40994A",
                darkBlue: "#0044F2",
                darkPink: "#F85156",
            },

            fontFamily: {
                poppins: ["Poppins"],
                dmserif: ["DM Serif Display"],
            },
        },
    },

    plugins: [],

    safelist: [
        // {
        //     pattern: /icon-/,
        // },
        "justify-start",
        "justify-center",
        "justify-end",
        "items-start",
        "items-center",
        "items-end",
        "bg-[#057EB5]",
        "bg-[#00138D]",
        "bg-[#8A0156]",
        "bg-[#EB6B2B]",
        "bg-[#232325]",
        "bg-[#8A33EE]",
        "bg-[#FA1432]",
        "bg-[#305E9B]",
        "bg-[#83BE01]",
        "bg-[#DEB229]",
        "bg-[#A8A8A8]",
        "bg-[#F0F0F2]",
        "bg-[#7DFC01]",
        "bg-[#FFFD03]",
        "bg-[#FEC708]",
        "bg-[#563E3B]",
        "bg-[#000000]",
        "border-[#057EB5]",
        "border-[#00138D]",
        "border-[#8A0156]",
        "border-[#EB6B2B]",
        "border-[#232325]",
        "border-[#8A33EE]",
        "border-[#FA1432]",
        "border-[#305E9B]",
        "border-[#83BE01]",
        "border-[#DEB229]",
        "border-[#A8A8A8]",
        "border-[#F0F0F2]",
        "border-[#7DFC01]",
        "border-[#FFFD03]",
        "border-[#FEC708]",
        "border-[#563E3B]",
        "border-[#000000]",
    ],
};
