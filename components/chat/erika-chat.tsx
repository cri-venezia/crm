"use client";

import { useEffect, useRef, useState } from "react";
import ReactMarkdown from 'react-markdown';
import { Send, Bot, User, Loader2, Trash2, Venus, HeartPulse, Paperclip, Sparkles } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { ScrollArea } from "@/components/ui/scroll-area";
import { cn } from "@/lib/utils";
import { useChatStore } from "@/store/chat-store";

export function ErikaChat() {
    const {
        messages,
        addMessage,
        setMessages,
        isLoading,
        setIsLoading,
        loadHistory,
        clearHistory,
        setUserId,
        userId
    } = useChatStore();

    const [input, setInput] = useState("");
    const [attachedFile, setAttachedFile] = useState<{ name: string, content: string } | null>(null);
    const [showQuickActions, setShowQuickActions] = useState(false);
    const bottomRef = useRef<HTMLDivElement>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        const getUser = async () => {
            const { createSupabaseClient } = await import('@/lib/supabase');
            const supabase = createSupabaseClient();
            const { data: { user } = { user: null } } = await supabase.auth.getUser(); // Added default value for data.user
            if (user) {
                setUserId(user.id);
                loadHistory(); // Load history once user is set
            }
        };
        getUser();
    }, []); // eslint-disable-next-line react-hooks/exhaustive-deps

    useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: "smooth" });
    }, [messages]);

    const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            if (file.type !== "text/plain") {
                alert("Per ora puoi caricare solo file di testo (.txt)");
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                const content = e.target?.result as string;
                setAttachedFile({ name: file.name, content });
            };
            reader.readAsText(file);
        }
    };

    const handleQuickAction = (actionPrompt: string) => {
        setInput(actionPrompt);
        setShowQuickActions(false);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if ((!input.trim() && !attachedFile) || isLoading) return;

        let finalInput = input;
        if (attachedFile) {
            finalInput = `[CONTESTO DAL FILE: ${attachedFile.name}]\n${attachedFile.content} \n\n[MESSAGGIO UTENTE]: \n${input} `;
        }

        const userMessageDisplay = { role: "user" as const, content: input || `(File inviato: ${attachedFile?.name})` };
        addMessage(userMessageDisplay);

        setInput("");
        setAttachedFile(null);
        if (fileInputRef.current) fileInputRef.current.value = "";

        setIsLoading(true);

        try {
            const response = await fetch("/api/erika/chat", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    message: finalInput,
                    history: messages, // Send current history
                    userId: userId || "anon-user"
                }),
            });

            if (!response.ok) {
                throw new Error("Network response was not ok");
            }

            const data = await response.json();
            const aiMessage = { role: "model" as const, content: data.response };
            addMessage(aiMessage);
        } catch (error) {
            console.error("Error sending message:", error);
            addMessage({ role: "model", content: "Mi dispiace, si è verificato un errore. Riprova più tardi." });
        } finally {
            setIsLoading(false);
        }
    };

    const handleClearChat = async () => {
        if (confirm("Sei sicuro di voler cancellare tutta la cronologia?")) {
            await clearHistory();
        }
    }

    const quickActions = [
        { label: "📧 Bozza Newsletter", prompt: "Scrivi una bozza per la newsletter del mese su: " },
        { label: "💰 Idee Fundraising", prompt: "Dammi 3 idee originali per una campagna di raccolta fondi riguardante: " },
        { label: "🆘 Cerca Volontari", prompt: "Controlla se ci sono volontari disponibili per: " },
    ];

    return (
        <Card className="w-full h-[600px] flex flex-col shadow-xl border-t-4 border-t-red-600">
            <CardHeader className="bg-slate-50 dark:bg-slate-900 border-b">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="p-2 bg-red-100 dark:bg-red-900/30 rounded-full">
                            <Venus className="w-6 h-6 text-red-600 dark:text-red-500" />
                        </div>
                        <div>
                            <CardTitle>Parla con Erika</CardTitle>
                            <CardDescription>
                                Assistente Operativa CRI Venezia
                            </CardDescription>
                        </div>
                    </div>
                    {userId && messages.length > 0 && (
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={handleClearChat}
                            title="Cancella cronologia"
                            className="text-muted-foreground hover:text-red-600"
                        >
                            <Trash2 className="w-4 h-4" />
                        </Button>
                    )}
                </div>
            </CardHeader>
            <CardContent className="flex-1 p-0 flex flex-col overflow-hidden">
                <ScrollArea className="flex-1 p-4">
                    <div className="space-y-4">
                        {messages.length === 0 && (
                            <div className="text-center text-muted-foreground py-10 space-y-4">
                                <div>
                                    <p className="text-lg font-medium text-gray-900 dark:text-gray-100">Ciao! Sono Erika.</p>
                                    <p className="text-sm">Come posso aiutarti oggi?</p>
                                </div>
                            </div>
                        )}
                        {messages.map((msg, idx) => (
                            <div
                                key={idx}
                                className={cn(
                                    "flex w-full",
                                    msg.role === "user" ? "justify-end" : "justify-start"
                                )}
                            >
                                <div
                                    className={cn(
                                        "flex max-w-[80%] gap-2 p-3 rounded-lg text-sm",
                                        msg.role === "user"
                                            ? "bg-red-600 text-white rounded-br-none"
                                            : "bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-bl-none"
                                    )}
                                >
                                    {msg.role === "model" && <Venus className="w-4 h-4 mt-0.5 shrink-0" />}
                                    <div className={cn(
                                        "prose prose-sm max-w-none break-words",
                                        msg.role === "user" ? "prose-invert prose-p:text-white prose-headings:text-white prose-strong:text-white text-white" : "dark:prose-invert"
                                    )}>
                                        <ReactMarkdown>{msg.content}</ReactMarkdown>
                                    </div>
                                    {msg.role === "user" && <User className="w-4 h-4 mt-0.5 shrink-0" />}
                                </div>
                            </div>
                        ))}
                        {isLoading && (
                            <div className="flex w-full justify-start">
                                <div className="flex max-w-[80%] gap-2 p-3 rounded-lg bg-slate-100 dark:bg-slate-800 rounded-bl-none">
                                    <HeartPulse className="w-4 h-4 mt-0.5 animate-pulse" />
                                    <div className="flex items-center gap-1">
                                        <span className="text-xs text-muted-foreground">Sto scrivendo...</span>
                                        <HeartPulse className="w-3 h-3 animate-pulse text-muted-foreground" />
                                    </div>
                                </div>
                            </div>
                        )}
                        <div ref={bottomRef} />
                    </div>
                </ScrollArea>

                {attachedFile && (
                    <div className="px-4 py-2 bg-gray-50 dark:bg-gray-900 border-t flex items-center gap-2">
                        <div className="bg-white dark:bg-gray-800 border px-3 py-1 rounded-md text-xs flex items-center gap-2 shadow-sm">
                            <span className="truncate max-w-[200px] font-medium">{attachedFile.name}</span>
                            <button onClick={() => setAttachedFile(null)} className="text-gray-400 hover:text-red-500">
                                <Trash2 className="w-3 h-3" />
                            </button>
                        </div>
                    </div>
                )}

                <div className="p-4 border-t bg-white dark:bg-slate-950 flex flex-col gap-3">
                    {showQuickActions && (
                        <div className="flex gap-2 overflow-x-auto pb-1 no-scrollbar -mx-2 px-2 animate-in slide-in-from-bottom-2 fade-in">
                            {quickActions.map((action, idx) => (
                                <Button
                                    key={idx}
                                    variant="outline"
                                    size="sm"
                                    className="whitespace-nowrap text-xs rounded-full border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-900 dark:text-red-400 bg-white"
                                    onClick={() => handleQuickAction(action.prompt)}
                                >
                                    {action.label}
                                </Button>
                            ))}
                        </div>
                    )}
                    <form onSubmit={handleSubmit} className="flex gap-2">
                        <input
                            type="file"
                            ref={fileInputRef}
                            className="hidden"
                            accept=".txt"
                            onChange={handleFileSelect}
                        />
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            className="shrink-0 text-muted-foreground hover:text-red-600"
                            onClick={() => fileInputRef.current?.click()}
                            title="Allega file di testo (.txt)"
                        >
                            <Paperclip className="w-5 h-5" />
                        </Button>
                        <Button
                            type="button"
                            variant={showQuickActions ? "secondary" : "ghost"}
                            size="icon"
                            className={cn("shrink-0 text-muted-foreground hover:text-red-600", showQuickActions && "text-red-600 bg-red-50")}
                            onClick={() => setShowQuickActions(!showQuickActions)}
                            title="Suggerimenti rapidi"
                        >
                            <Sparkles className="w-5 h-5" />
                        </Button>
                        <Input
                            placeholder={attachedFile ? "Scrivi cosa fare con il file..." : "Scrivi un messaggio a Erika..."}
                            value={input}
                            onChange={(e) => setInput(e.target.value)}
                            disabled={isLoading}
                            className="flex-1"
                        />
                        <Button type="submit" size="icon" disabled={(isLoading || (!input.trim() && !attachedFile))}>
                            <Send className="w-4 h-4" />
                            <span className="sr-only">Invia</span>
                        </Button>
                    </form>
                </div>
            </CardContent>
        </Card>
    );
}
