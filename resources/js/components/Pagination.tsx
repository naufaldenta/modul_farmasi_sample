import { Link } from '@inertiajs/react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationProps {
    links: PaginationLink[];
}

export default function Pagination({ links }: PaginationProps) {
    if (links.length <= 3) return null; // If only Previous, Next, and Page 1 exist, no need to show pagination

    return (
        <div className="flex flex-wrap -mb-1 mt-4 justify-end gap-1">
            {links.map((link, index) => (
                link.url === null ? (
                    <div
                        key={index}
                        className="mr-1 mb-1 px-4 py-2 text-sm leading-4 text-muted-foreground border rounded bg-muted cursor-not-allowed"
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <Link
                        key={index}
                        className={`mr-1 mb-1 px-4 py-2 text-sm leading-4 border rounded hover:bg-muted focus:border-primary focus:text-primary ${
                            link.active ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'bg-background'
                        }`}
                        href={link.url}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                )
            ))}
        </div>
    );
}
