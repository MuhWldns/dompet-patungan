import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-semibold transition-colors disabled:pointer-events-none disabled:opacity-40 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/15 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        default:
          "bg-primary text-primary-foreground hover:bg-[#020617] dark:hover:bg-primary/90",
        destructive:
          "bg-destructive text-white hover:bg-red-600 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40",
        outline:
          "border border-primary bg-transparent text-primary shadow-none hover:bg-primary/5 dark:border-primary dark:bg-transparent dark:text-primary-foreground dark:hover:bg-primary/10",
        secondary:
          "bg-transparent border border-primary text-primary hover:bg-primary/5 dark:border-primary dark:text-primary-foreground dark:hover:bg-primary/10",
        ghost:
          "text-muted-foreground hover:bg-muted hover:text-foreground dark:hover:bg-accent/50",
        link: "text-tertiary underline-offset-4 hover:underline",
      },
      size: {
        "default": "h-[42px] px-[22px] py-2.5 has-[>svg]:px-4",
        "sm": "h-8 rounded-lg gap-1.5 px-3.5 py-1.5 has-[>svg]:px-3",
        "lg": "h-12 rounded-lg px-7 py-3 text-base has-[>svg]:px-5",
        "icon": "size-9",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
