import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { ArrowLeft, MapPin, User, Store, Package, DollarSign, Clock } from 'lucide-react';

interface OrderDetailProps {
    order: {
        id: number;
        order_number: string;
        status: string;
        subtotal: number;
        tax: number;
        delivery_fee: number;
        service_fee: number;
        tip: number | null;
        discount: number;
        total: number;
        payment_method: string;
        payment_status: string;
        delivery_instructions: string | null;
        scheduled_delivery_time: string | null;
        delivered_at: string | null;
        created_at: string;
        customer: {
            id: number;
            name: string;
            email: string;
            phone?: string;
        };
        merchant: {
            id: number;
            business_name: string;
            email: string;
            phone: string;
            street_address: string;
            city: string;
            state: string;
            zip_code: string;
        };
        batch: {
            id: number;
            batch_number: string;
            status: string;
            shopper: {
                user: {
                    name: string;
                    email: string;
                };
            };
        } | null;
        address: {
            label: string | null;
            street_address: string;
            apartment: string | null;
            city: string;
            state: string;
            zip_code: string;
        };
        items: Array<{
            id: number;
            product_name: string;
            product_price: number;
            quantity: number;
            unit: string;
            total: number;
            special_instructions: string | null;
        }>;
    };
}

export default function OrderDetail({ order }: OrderDetailProps) {
    const getStatusColor = (status: string) => {
        const colors: Record<string, string> = {
            pending: 'bg-yellow-100 text-yellow-800',
            confirmed: 'bg-blue-100 text-blue-800',
            preparing: 'bg-purple-100 text-purple-800',
            ready_for_pickup: 'bg-indigo-100 text-indigo-800',
            picked_up: 'bg-cyan-100 text-cyan-800',
            in_transit: 'bg-orange-100 text-orange-800',
            delivered: 'bg-green-100 text-green-800',
            cancelled: 'bg-red-100 text-red-800',
            refunded: 'bg-gray-100 text-gray-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin' },
        { title: 'Orders', href: '/admin/orders' },
        { title: order.order_number, href: `/admin/orders/${order.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Order ${order.order_number}`} />
            <div className="p-6 space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link href="/admin/orders">
                            <Button variant="ghost" size="icon">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-3xl font-bold">{order.order_number}</h1>
                            <p className="text-muted-foreground">
                                Created {new Date(order.created_at).toLocaleString()}
                            </p>
                        </div>
                    </div>
                    <Badge className={`text-lg px-4 py-2 ${getStatusColor(order.status)}`}>
                        {order.status.replace('_', ' ')}
                    </Badge>
                </div>

                {/* Order Summary Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Customer</CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="font-medium">{order.customer.name}</div>
                            <div className="text-sm text-muted-foreground">{order.customer.email}</div>
                            {order.customer.phone && (
                                <div className="text-sm text-muted-foreground">{order.customer.phone}</div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Merchant</CardTitle>
                            <Store className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="font-medium">{order.merchant.business_name}</div>
                            <div className="text-sm text-muted-foreground">{order.merchant.email}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Assigned Shopper</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            {order.batch?.shopper ? (
                                <>
                                    <div className="font-medium">{order.batch.shopper.user.name}</div>
                                    <div className="text-sm text-muted-foreground">
                                        Batch: {order.batch.batch_number}
                                    </div>
                                </>
                            ) : (
                                <div className="text-muted-foreground">Unassigned</div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">Total Amount</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">${order.total.toFixed(2)}</div>
                            <div className="text-sm text-muted-foreground">
                                {order.payment_method} - {order.payment_status}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Delivery Address */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle>Delivery Address</CardTitle>
                            <MapPin className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            {order.address.label && (
                                <Badge variant="outline" className="mb-2">
                                    {order.address.label}
                                </Badge>
                            )}
                            <div className="font-medium">
                                {order.address.street_address}
                            </div>
                            {order.address.apartment && (
                                <div className="text-muted-foreground">
                                    {order.address.apartment}
                                </div>
                            )}
                            <div className="text-muted-foreground">
                                {order.address.city}, {order.address.state} {order.address.zip_code}
                            </div>
                            {order.delivery_instructions && (
                                <div className="mt-4 p-3 bg-muted rounded-lg">
                                    <div className="text-sm font-medium mb-1">Delivery Instructions:</div>
                                    <div className="text-sm text-muted-foreground">
                                        {order.delivery_instructions}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Order Timeline */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle>Timeline</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Order Placed</span>
                                <span>{new Date(order.created_at).toLocaleString()}</span>
                            </div>
                            {order.scheduled_delivery_time && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Scheduled Delivery</span>
                                    <span>{new Date(order.scheduled_delivery_time).toLocaleString()}</span>
                                </div>
                            )}
                            {order.delivered_at && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Delivered</span>
                                    <span>{new Date(order.delivered_at).toLocaleString()}</span>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Order Items */}
                <Card>
                    <CardHeader>
                        <CardTitle>Order Items</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Product</TableHead>
                                    <TableHead>Price</TableHead>
                                    <TableHead>Qty</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Notes</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {order.items.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell className="font-medium">{item.product_name}</TableCell>
                                        <TableCell>${item.product_price.toFixed(2)} / {item.unit}</TableCell>
                                        <TableCell>{item.quantity}</TableCell>
                                        <TableCell>${item.total.toFixed(2)}</TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {item.special_instructions || '-'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Order Summary */}
                        <div className="mt-6 flex justify-end">
                            <div className="w-full max-w-sm space-y-2">
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Subtotal</span>
                                    <span>${order.subtotal.toFixed(2)}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Tax</span>
                                    <span>${order.tax.toFixed(2)}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Delivery Fee</span>
                                    <span>${order.delivery_fee.toFixed(2)}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Service Fee</span>
                                    <span>${order.service_fee.toFixed(2)}</span>
                                </div>
                                {order.tip && (
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Tip</span>
                                        <span>${order.tip.toFixed(2)}</span>
                                    </div>
                                )}
                                {order.discount > 0 && (
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Discount</span>
                                        <span>-${order.discount.toFixed(2)}</span>
                                    </div>
                                )}
                                <div className="border-t pt-2 flex justify-between font-bold text-lg">
                                    <span>Total</span>
                                    <span>${order.total.toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
