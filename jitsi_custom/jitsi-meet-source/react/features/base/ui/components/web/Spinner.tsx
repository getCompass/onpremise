import React from 'react';
import { keyframes } from 'tss-react';
import { makeStyles } from 'tss-react/mui';

interface IProps {
    color?: string;
    size?: 'small' | 'medium' | 'large';
}

const SIZE = {
    small: 16,
    medium: 24,
    large: 48
};

const DEFAULT_COLOR = '#E6EDFA';

const useStyles = makeStyles<{ color?: string; }>()((_, { color }) => {
    return {
        container: {
            verticalAlign: 'middle',
            opacity: 0,
            animation: `${keyframes`
                0% {
                    transform: rotate(50deg);
                    opacity: 0;
                    stroke-dashoffset: 60;
                }
                100% {
                    transform: rotate(230deg);
                    opacity: 1;
                    stroke-dashoffset: 50;
                }
            `} 1s forwards ease-in-out`
        },

        circle: {
            transformOrigin: 'center',
            animation: `${keyframes`
                0% {
                    transform: rotate(0);
                }
                100% {
                    transform: rotate(360deg);
                }
            `} 0.5s linear infinite`,
            animationDelay: '0ms'
        }
    };
});

const Spinner = ({ color = DEFAULT_COLOR, size = 'medium' }: IProps) => {
    const { classes } = useStyles({ color });

    return (
        <div className = {classes.container}
             style = {{
                 width: SIZE[size],
                 height: SIZE[size],
             }}>
            <svg className = {classes.circle} focusable = 'false' width = {SIZE[size]} height = {SIZE[size]}
                 viewBox = "0 0 20 20" fill = "none" xmlns = "http://www.w3.org/2000/svg">
                <path
                    d = "M10 1.175C10 0.725 10 0.475 10 0C7.325 0 4.825 1.05 2.925 2.925C1.05 4.825 0 7.325 0 10C0.4 10 0.775 10 1.175 10C1.175 5.125 5.125 1.175 10 1.175Z"
                    fill = "url(#paint0_linear_9773_15393)" />
                <path
                    d = "M10 1.175C10 0.725 10 0.475 10 0C12.675 0 15.175 1.05 17.075 2.925C18.95 4.825 20 7.325 20 10C19.6 10 19.225 10 18.825 10C18.825 5.125 14.875 1.175 10 1.175Z"
                    fill = "url(#paint1_linear_9773_15393)" />
                <path
                    d = "M18.825 10C18.825 14.85 14.85 18.825 10 18.825C5.125 18.825 1.175 14.875 1.175 10C0.775 10 0.4 10 0 10C0 12.675 1.05 15.175 2.925 17.075C4.825 18.95 7.325 20 10 20C12.675 20 15.175 18.95 17.075 17.075C18.95 15.175 20 12.675 20 10H18.825Z"
                    fill = "white" fill-opacity = "0.6" />
                <defs>
                    <linearGradient id = "paint0_linear_9773_15393" x1 = "4.375" y1 = "2.5" x2 = "-1.875" y2 = "8"
                                    gradientUnits = "userSpaceOnUse">
                        <stop offset = "0.046875" stop-color = "white" stop-opacity = "0" />
                        <stop offset = "0.364583" stop-color = "white" stop-opacity = "0.2567" />
                        <stop offset = "1" stop-color = "white" stop-opacity = "0.6" />
                    </linearGradient>
                    <linearGradient id = "paint1_linear_9773_15393" x1 = "15.625" y1 = "2.5" x2 = "21.875" y2 = "8"
                                    gradientUnits = "userSpaceOnUse">
                        <stop offset = "0.046875" stop-color = "white" stop-opacity = "0" />
                        <stop offset = "0.364583" stop-color = "white" stop-opacity = "0.2567" />
                        <stop offset = "1" stop-color = "white" stop-opacity = "0.6" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
    );
};

export default Spinner;
