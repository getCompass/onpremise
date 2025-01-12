import clsx from "clsx";
import React, { Component, ReactNode } from "react";

/**
 * The type of the React {@code Component} props of {@link OverlayFrameMobile}.
 */
interface IProps {

    /**
     * The children components to be displayed into the overlay frame.
     */
    children: ReactNode;

    /**
     * Indicates the css style of the overlay. If true, then lighter; darker,
     * otherwise.
     */
    isLightOverlay?: boolean;

    /**
     * The style property.
     */
    style?: Object;

    contentPosition?: 'top' | 'middle' | 'bottom';
}

/**
 * Implements a React {@link Component} for the frame of the overlays.
 */
export default class OverlayFrameMobile extends Component<IProps> {
    /**
     * Implements React's {@link Component#render()}.
     *
     * @inheritdoc
     * @returns {ReactElement|null}
     */
    render() {
        return (
            <div
                className = {this.props.isLightOverlay ? "overlay_mobile__container-light" : "overlay_mobile__container"}
                id = "overlay"
                style = {this.props.style}
            >
                <div
                    className = {clsx(
                        "overlay_mobile__content",
                        this.props.contentPosition ? `overlay_mobile__content_${this.props.contentPosition}` : ""
                    )}
                >
                    {this.props.children}
                </div>
            </div>
        );
    }
}
