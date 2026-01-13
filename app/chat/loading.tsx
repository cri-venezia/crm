import { Skeleton } from "@/components/ui/skeleton";

export default function Loading() {
    return (
        <main className="container mx-auto p-4 md:p-8 h-[calc(100vh-4rem)] flex flex-col">
            <div className="flex items-center space-x-2 mb-4">
                <Skeleton className="h-10 w-48" />
            </div>
            <div className="flex-1 overflow-hidden border rounded-lg p-4 space-y-4">
                <div className="flex justify-start">
                    <Skeleton className="h-12 w-64 rounded-lg" />
                </div>
                <div className="flex justify-end">
                    <Skeleton className="h-12 w-64 rounded-lg" />
                </div>
                <div className="flex justify-start">
                    <Skeleton className="h-24 w-full max-w-lg rounded-lg" />
                </div>
            </div>
        </main>
    );
}
