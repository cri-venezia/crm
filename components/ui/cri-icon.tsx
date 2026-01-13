import Image from 'next/image';
import { cn } from '@/lib/utils';

export const solidFullIcons = [
    'heart-circle-bolt',
    'heart-circle-check',
    'heart-circle-exclamation',
    'heart-circle-minus',
    'heart-circle-plus',
    'heart-circle-xmark',
    'heart-pulse',
    'house-chimney-medical',
    'house-medical-circle-check',
    'house-medical-circle-exclamation',
    'house-medical-circle-xmark',
    'house-medical-flag',
    'house-medical',
    'id-card-clip',
    'kit-medical',
    'laptop-medical',
    'staff-snake',
    'star-of-life',
    'stethoscope',
    'user-nurse'
] as const;

export type IconName = typeof solidFullIcons[number] | 'anagrafica' | 'login' | 'login-hover';

interface CriIconProps {
    name: IconName | string; // Allow string to be more flexible, but prefer IconName
    className?: string;
    size?: number;
}

export function CriIcon({ name, className, size = 24 }: CriIconProps) {
    // Check if it's one of the legacy solid-full icons
    const isSolidFull = solidFullIcons.includes(name as any);
    const src = isSolidFull ? `/icons/${name}-solid-full.svg` : `/icons/${name}.svg`;

    return (
        <div className={cn("relative inline-block", className)} style={{ width: size, height: size }}>
            <Image
                src={src}
                alt={name}
                fill
                className="object-contain"
            />
        </div>
    );
}
