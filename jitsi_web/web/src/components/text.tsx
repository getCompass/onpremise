import type {ComponentPropsWithoutRef} from "react"
import {styled} from "../../styled-system/jsx"
import {text, type TextVariantProps} from "../../styled-system/recipes"

export type TextProps = TextVariantProps & ComponentPropsWithoutRef<typeof styled.div>
export const Text = styled(styled.div, text)
