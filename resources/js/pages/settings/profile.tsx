import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { toast } from 'sonner';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import { FormEventHandler, useRef, useState, useEffect } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

type ProfileForm = {
    name: string;
    email: string;
};

export default function Profile({
    mustVerifyEmail,
    status,
    avatarUrl
}: {
    mustVerifyEmail: boolean;
    status?: string;
    avatarUrl?: string | null;
}) {
    const { auth } = usePage<SharedData>().props;
    const { errors: pageErrors, status: flashStatus } = usePage().props as any;

    // toast for flash messages
    useEffect(() => {
        if (flashStatus && typeof flashStatus === 'string') {
            toast.success(flashStatus);
        }
        if (pageErrors?.social) {
            toast.error(pageErrors.social as string);
        }
    }, [flashStatus, pageErrors]);

    const fileInputRef = useRef<HTMLInputElement>(null);
    const [uploadingAvatar, setUploadingAvatar] = useState(false);

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm<Required<ProfileForm>>({
        name: auth.user.name,
        email: auth.user.email,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    const handleAvatarUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        setUploadingAvatar(true);

        const formData = new FormData();
        formData.append('avatar', file);

        // Using Inertia router to upload the file
        router.post(route('profile.avatar.upload'), formData, {
            onSuccess: () => {
                setUploadingAvatar(false);
                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
            onError: () => {
                setUploadingAvatar(false);
            },
        });
    };

    const handleDeleteAvatar = () => {
        if (confirm('Are you sure you want to delete your avatar?')) {
            router.delete(route('profile.avatar.delete'));
        }
    };

    const getUserInitials = (name: string) => {
        return name
            .split(' ')
            .map(n => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    {/* Avatar Section */}
                    <div className="space-y-4">
                        <HeadingSmall title="Profile Photo" description="Update your profile picture" />

                        <div className="flex items-center gap-6">
                            <Avatar className="h-20 w-20">
                                {avatarUrl ? (
                                    <AvatarImage src={avatarUrl} alt={auth.user.name} />
                                ) : (
                                    <AvatarFallback className="text-lg font-semibold">
                                        {getUserInitials(auth.user.name)}
                                    </AvatarFallback>
                                )}
                            </Avatar>

                            <div className="flex gap-3">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => fileInputRef.current?.click()}
                                    disabled={uploadingAvatar}
                                >
                                    {uploadingAvatar ? 'Uploading...' : 'Upload Photo'}
                                </Button>

                                {avatarUrl && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handleDeleteAvatar}
                                    >
                                        Delete Photo
                                    </Button>
                                )}
                            </div>

                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/*"
                                onChange={handleAvatarUpload}
                                className="hidden"
                            />
                        </div>

                        <p className="text-sm text-muted-foreground">
                            JPG, PNG, GIF up to 2MB
                        </p>
                    </div>

                    <HeadingSmall title="Profile information" description="Update your name and email address" />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Name</Label>

                            <Input
                                id="name"
                                className="mt-1 block w-full"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                required
                                autoComplete="name"
                                placeholder="Full name"
                            />

                            <InputError className="mt-2" message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email address</Label>

                            <Input
                                id="email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                                autoComplete="username"
                                placeholder="Email address"
                            />

                            <InputError className="mt-2" message={errors.email} />
                        </div>

                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div>
                                <p className="-mt-4 text-sm text-muted-foreground">
                                    Your email address is unverified.{' '}
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                    >
                                        Click here to resend the verification email.
                                    </Link>
                                </p>

                                {status === 'verification-link-sent' && (
                                    <div className="mt-2 text-sm font-medium text-green-600">
                                        A new verification link has been sent to your email address.
                                    </div>
                                )}
                            </div>
                        )}

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Save</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Saved</p>
                            </Transition>
                        </div>
                    </form>

                    {/* Social Accounts Section */}
                    <HeadingSmall title="Social accounts" description="Connect or disconnect third-party accounts" />

                    <div className="space-y-2">
                        {auth.user.provider_name === 'github' ? (
                            <div className="flex items-center gap-4">
                                <p className="text-sm">GitHub account connected</p>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        if (confirm('Disconnect GitHub account?')) {
                                            router.delete(route('github.unlink'), {
                                                preserveScroll: true,
                                                onSuccess: () => toast.success('GitHub account disconnected'),
                                            });
                                        }
                                    }}
                                >
                                    Disconnect
                                </Button>
                            </div>
                        ) : (
                            <Button variant="outline" asChild>
                                <a href={route('github.redirect')}>Connect GitHub</a>
                            </Button>
                        )}
                        {auth.user.provider_name === 'google' ? (
                            <div className="flex items-center gap-4">   
                                <p className="text-sm">Google account connected</p>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        if (confirm('Disconnect Google account?')) {
                                            router.delete(route('google.unlink'), {
                                                preserveScroll: true,
                                                onSuccess: () => toast.success('Google account disconnected'),
                                            });
                                        }
                                    }}
                                >
                                    Disconnect
                                </Button>
                            </div>
                        ) : (
                            <Button variant="outline" asChild>
                                <a href={route('google.redirect')}>Connect Google</a>
                            </Button>
                        )}
                    </div>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
