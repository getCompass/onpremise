import {Box} from "../../styled-system/jsx";

const Preloader16 = () => {

    return (
        <Box
            animation="spin500ms"
            w="16px"
            h="16px"
        >
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M8 0.94C8 0.58 8 0.38 8 0C5.86 0 3.86 0.84 2.34 2.34C0.84 3.86 0 5.86 0 8C0.32 8 0.62 8 0.94 8C0.94 4.1 4.1 0.94 8 0.94Z"
                    fill="url(#paint0_linear_1386_30999)"/>
                <path
                    d="M8 0.94C8 0.58 8 0.38 8 0C10.14 0 12.14 0.84 13.66 2.34C15.16 3.86 16 5.86 16 8C15.68 8 15.38 8 15.06 8C15.06 4.1 11.9 0.94 8 0.94Z"
                    fill="url(#paint1_linear_1386_30999)"/>
                <path
                    d="M15.06 8C15.06 11.88 11.88 15.06 8 15.06C4.1 15.06 0.94 11.9 0.94 8C0.62 8 0.32 8 0 8C0 10.14 0.84 12.14 2.34 13.66C3.86 15.16 5.86 16 8 16C10.14 16 12.14 15.16 13.66 13.66C15.16 12.14 16 10.14 16 8H15.06Z"
                    fill="white"/>
                <defs>
                    <linearGradient id="paint0_linear_1386_30999" x1="3.5" y1="2" x2="-1.5" y2="6.4"
                                    gradientUnits="userSpaceOnUse">
                        <stop offset="0.046875" stopColor="white" stopOpacity="0"/>
                        <stop offset="0.364583" stopColor="white" stopOpacity="0.2567"/>
                        <stop offset="1" stopColor="white"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear_1386_30999" x1="12.5" y1="2" x2="17.5" y2="6.4"
                                    gradientUnits="userSpaceOnUse">
                        <stop offset="0.046875" stopColor="white" stopOpacity="0"/>
                        <stop offset="0.364583" stopColor="white" stopOpacity="0.2567"/>
                        <stop offset="1" stopColor="white"/>
                    </linearGradient>
                </defs>
            </svg>
        </Box>
    );
}

export default Preloader16;