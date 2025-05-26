import React, { useState, useEffect } from 'react';
import { makeStyles } from 'tss-react/mui';

const useStyles = makeStyles()(theme => {
    return {
        wrapper: {
            position: 'fixed',
            top: 0,
            left: 0,
            opacity: 0,
            pointerEvents: 'none',
        },
    };
});

interface TempFontType {
    onRemove: () => void;
}


const TemporaryFont = ({ onRemove }: TempFontType) => {
    const { classes } = useStyles();
    useEffect(() => {
        const timer = setTimeout(() => {
            onRemove();
        }, 3000);

        return () => clearTimeout(timer);
    }, []);

    return <div className={ classes.wrapper }>
        <div className="font-lato-regular">font-lato-regular</div>
        <div className="font-lato-medium">font-lato-medium</div>
        <div className="font-lato-semi-bold">font-lato-semi-bold</div>
        <div className="font-lato-bold">font-lato-bold</div>
        <div className="font-lato-black">font-lato-black</div>
        <div className="font-inter-regular">font-inter-regular</div>
        <div className="font-inter-medium">font-inter-medium</div>
        <div className="font-inter-semi-bold">font-inter-semi-bold</div>
        <div className="font-inter-bold">font-inter-bold</div>
        <div className="font-inter-black">font-inter-black</div>
    </div>;
};

const FontPreload = () => {
    const [showComponent, setShowComponent] = useState(true);
    return (<div>
            {showComponent && <TemporaryFont onRemove={() => setShowComponent(false)}/>}
        </div>
    );
};

export default FontPreload;
