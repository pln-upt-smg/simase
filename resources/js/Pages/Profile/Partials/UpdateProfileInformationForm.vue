<template>
    <jet-form-section @submitted="updateProfileInformation">
        <template #title>
            Informasi Profil
        </template>
        <template #description>
            Perbarui informasi profil dan nama akun Anda.
        </template>
        <template #form>
            <div class="col-span-6 sm:col-span-4" v-if="$page.props.jetstream.managesProfilePhotos">
                <input type="file" class="hidden"
                       ref="photo"
                       @change="updatePhotoPreview">
                <jet-label for="photo" value="Foto Profil"/>
                <div class="mt-2" v-show="! photoPreview">
                    <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full h-20 w-20 object-cover">
                </div>
                <div class="mt-2" v-show="photoPreview">
                    <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                          :style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>
                <jet-secondary-button class="mt-4 mr-2" type="button" @click.prevent="selectNewPhoto">
                    Pilih Foto Baru
                </jet-secondary-button>
                <jet-secondary-button type="button" class="mt-2" @click.prevent="deletePhoto"
                                      v-if="user.profile_photo_path">
                    Hapus Foto
                </jet-secondary-button>
                <jet-input-error :message="form.errors.photo" class="mt-2"/>
            </div>
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="name" value="Nama"/>
                <jet-input id="name" type="text" class="mt-1 block w-full" v-model="form.name" autocomplete="name"/>
                <jet-input-error :message="form.errors.name" class="mt-2"/>
            </div>
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="phone" value="Nomor Telepon"/>
                <jet-input id="phone" type="text" class="mt-1 block w-full" v-model="form.phone" autocomplete="phone"/>
                <jet-input-error :message="form.errors.phone" class="mt-2"/>
            </div>
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="nip" value="Nomor Induk Pegawai"/>
                <jet-input id="nip" type="text" class="mt-1 block w-full bg-gray-100 cursor-not-allowed"
                           v-model="form.nip" disabled/>
                <jet-input-error :message="form.errors.nip" class="mt-2"/>
            </div>
        </template>
        <template #actions>
            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                Profil berhasil diperbarui.
            </jet-action-message>
            <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Simpan
            </jet-button>
        </template>
    </jet-form-section>
</template>

<script>
import {defineComponent} from 'vue'
import JetButton from '@/Jetstream/Button.vue'
import JetFormSection from '@/Jetstream/FormSection.vue'
import JetInput from '@/Jetstream/Input.vue'
import JetInputError from '@/Jetstream/InputError.vue'
import JetLabel from '@/Jetstream/Label.vue'
import JetActionMessage from '@/Jetstream/ActionMessage.vue'
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue'

export default defineComponent({
    components: {
        JetActionMessage,
        JetButton,
        JetFormSection,
        JetInput,
        JetInputError,
        JetLabel,
        JetSecondaryButton,
    },
    props: ['user'],
    data() {
        return {
            form: this.$inertia.form({
                _method: 'PUT',
                name: this.user.name,
                phone: this.user.phone,
                nip: this.user.nip,
                photo: null,
            }),
            photoPreview: null
        }
    },
    methods: {
        updateProfileInformation() {
            if (this.$refs.photo) {
                this.form.photo = this.$refs.photo.files[0]
            }
            this.form.post(route('user-profile-information.update'), {
                errorBag: 'updateProfileInformation',
                preserveScroll: true,
                onSuccess: () => (this.clearPhotoFileInput()),
            })
        },
        selectNewPhoto() {
            this.$refs.photo.click()
        },
        updatePhotoPreview() {
            const photo = this.$refs.photo.files[0]
            if (!photo) return
            const reader = new FileReader()
            reader.onload = (e) => {
                this.photoPreview = e.target.result;
            }
            reader.readAsDataURL(photo);
        },
        deletePhoto() {
            this.$inertia.delete(route('current-user-photo.destroy'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.photoPreview = null;
                    this.clearPhotoFileInput();
                }
            })
        },
        clearPhotoFileInput() {
            if (this.$refs.photo?.value) {
                this.$refs.photo.value = null;
            }
        }
    }
})
</script>
