import { SocialIcon } from "react-social-icons";
import { Button } from "@/components/ui/button";

export default function QuickAction() {
    return (
        <div className="mb-6 grid gap-4">
        <Button variant="outline" className="w-full" asChild>
            <a href="/auth/github/redirect" className="flex items-center justify-center">
                <SocialIcon style={{ height: '25px', width: '25px' }} className="mr-2" url="https://github.com" /> Continue with GitHub
            </a>
        </Button>
        <Button variant="outline" className="w-full" asChild>
            <a href="/auth/google/redirect" className="flex items-center justify-center">
                <SocialIcon style={{ height: '25px', width: '25px' }} className="mr-2" url="https://google.com" /> Continue with Google
            </a>
        </Button>

        <div className="relative">
            <div className="absolute inset-0 flex items-center">
                <span className="w-full border-t border-border" />
            </div>
            <div className="relative flex justify-center text-xs uppercase text-muted-foreground">
                <span className="bg-background px-2">Or sign up with</span>
            </div>
        </div>
    </div>
    );
}