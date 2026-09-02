import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

let shownMessages = new Set<string>()

export function initializeFlashToast() {

    // prevent SSR error
    if (typeof window === 'undefined') return

    const page = usePage()

    watch(
        () => page.props ?? {},
        (props: any) => {

            /**
             * =========================
             * FLASH SUCCESS
             * =========================
             */
            if (props?.flash?.success) {

                const message = props.flash.success

                if (!shownMessages.has(message)) {

                    toast.success(message)

                    shownMessages.add(message)

                    setTimeout(() => {
                        shownMessages.delete(message)
                    }, 1000)
                }
            }

            /**
             * =========================
             * FLASH ERROR
             * =========================
             */
            if (props?.flash?.error) {

                const message = props.flash.error

                if (!shownMessages.has(message)) {

                    toast.error(message)

                    shownMessages.add(message)

                    setTimeout(() => {
                        shownMessages.delete(message)
                    }, 1000)
                }
            }

            /**
             * =========================
             * VALIDATION ERRORS
             * =========================
             */
            if (props?.errors) {

                Object.values(props.errors).forEach((message: any) => {

                    const text = String(message)

                    if (!shownMessages.has(text)) {

                        toast.error(text)

                        shownMessages.add(text)

                        setTimeout(() => {
                            shownMessages.delete(text)
                        }, 1000)
                    }
                })
            }
        },
        {
            deep: true,
            immediate: true,
        }
    )
}