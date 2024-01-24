import {Box} from "../../styled-system/jsx";

const IconLogo = () => {

    return (
        <Box
            w="80px"
            h="80px"
            bgColor="white"
            rounded="16px"
            borderWidth="1px"
            borderColor="f5f5f5"
            display="flex"
            justifyContent="center"
            alignItems="center"
        >
            <svg width="40" height="55" viewBox="0 0 40 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fillRule="evenodd" clipRule="evenodd"
                      d="M17.3996 2.29332L0.582788 50.6417C-0.329216 53.2637 2.70581 55.4813 4.92668 53.8157L18.3685 43.7343C19.3685 42.9843 20.7435 42.9843 21.7435 43.7343L35.1853 53.8157C37.4062 55.4813 40.4412 53.2637 39.5292 50.6417L22.7124 2.29333C21.8366 -0.224722 18.2755 -0.224732 17.3996 2.29332Z"
                      fill="url(#paint0_linear_13_6798)"/>
                <defs>
                    <linearGradient id="paint0_linear_13_6798" x1="20.056" y1="0.404785" x2="20.056" y2="54.392"
                                    gradientUnits="userSpaceOnUse">
                        <stop stopColor="#FD5A09"/>
                        <stop offset="1" stopColor="#F33202"/>
                    </linearGradient>
                </defs>
            </svg>
        </Box>
    );
}

export default IconLogo;