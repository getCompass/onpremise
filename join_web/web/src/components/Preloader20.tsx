import {Box} from "../../styled-system/jsx";

const Preloader20 = () => {

    return (
        <Box
            animation="spin500ms"
            w="20px"
            h="20px"
        >
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M10 1.175C10 0.725 10 0.475 10 0C7.325 0 4.825 1.05 2.925 2.925C1.05 4.825 0 7.325 0 10C0.4 10 0.775 10 1.175 10C1.175 5.125 5.125 1.175 10 1.175Z"
                    fill="url(#paint0_linear_469_16067)"/>
                <path
                    d="M10 1.175C10 0.725 10 0.475 10 0C12.675 0 15.175 1.05 17.075 2.925C18.95 4.825 20 7.325 20 10C19.6 10 19.225 10 18.825 10C18.825 5.125 14.875 1.175 10 1.175Z"
                    fill="url(#paint1_linear_469_16067)"/>
                <path
                    d="M18.825 10C18.825 14.85 14.85 18.825 10 18.825C5.125 18.825 1.175 14.875 1.175 10C0.775 10 0.4 10 0 10C0 12.675 1.05 15.175 2.925 17.075C4.825 18.95 7.325 20 10 20C12.675 20 15.175 18.95 17.075 17.075C18.95 15.175 20 12.675 20 10H18.825Z"
                    fill="white"/>
                <defs>
                    <linearGradient id="paint0_linear_469_16067" x1="4.375" y1="2.5" x2="-1.875" y2="8"
                                    gradientUnits="userSpaceOnUse">
                        <stop offset="0.046875" stopColor="white" stopOpacity="0"/>
                        <stop offset="0.364583" stopColor="white" stopOpacity="0.2567"/>
                        <stop offset="1" stopColor="white"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear_469_16067" x1="15.625" y1="2.5" x2="21.875" y2="8"
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

export default Preloader20;