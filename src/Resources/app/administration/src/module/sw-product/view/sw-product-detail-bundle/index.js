import template from './sw-product-detail-bundle.html.twig';
import './index.scss';

const { Criteria } = Shopware.Data;

Shopware.Component.register('sw-product-detail-bundle', {
    template,

    inject: ['repositoryFactory'],

    data() {
        return {
            product: null,
            isLoading: false,
            bundles: [],
            selectedBundleId: null,
            selectedProduct: null,
            assignedProducts: []
        };
    },

    computed: {
        productId() {
            return this.$route.params.id;
        },

        selectedBundle() {
            return this.bundles.find((bundle) => bundle.id === this.selectedBundleId) || null;
        },

        bundleAssignedProductsRepository() {
            return this.repositoryFactory.create('product_bundle_assigned_products');
        },

        bundleRepository() {
            return this.repositoryFactory.create('product_bundle');
        }
    },

    async created() {
        await this.loadProduct();

        if (this.product?.id) {
            await this.loadBundles();
        }
    },

    methods: {
        async loadProduct() {
            const repo = this.repositoryFactory.create('product');

            try {
                this.product = await repo.get(this.productId, Shopware.Context.api);
            } catch (e) {
                console.error('Failed loading product:', e);
                this.product = null;
            }
        },

        async onProductSelect(productId) {
            if (!productId) {
                this.selectedProduct = null;
                return;
            }

            const productRepository = this.repositoryFactory.create('product');

            try {
                this.selectedProduct = await productRepository.get(productId, Shopware.Context.api);
            } catch (error) {
                console.error("Error fetching product:", error);
                this.selectedProduct = null;
            }
        },

        async loadBundles(selectBundleId = null) {
            if (!this.product?.id) {
                this.bundles = [];
                this.selectedBundleId = null;
                this.assignedProducts = [];

                return;
            }

            this.isLoading = true;

            try {
                const criteria = new Criteria()
                    .addFilter(Criteria.equals('productId', this.product.id));

                const bundles = await this.bundleRepository.search(criteria, Shopware.Context.api);
                this.bundles = [...bundles];

                const fallbackBundleId = this.bundles[0]?.id ?? null;
                const nextBundleId = selectBundleId && this.bundles.some((bundle) => bundle.id === selectBundleId)
                    ? selectBundleId
                    : this.selectedBundleId && this.bundles.some((bundle) => bundle.id === this.selectedBundleId)
                        ? this.selectedBundleId
                        : fallbackBundleId;

                this.selectedBundleId = nextBundleId;
                await this.loadAssignedProducts();
            } catch (error) {
                console.error('Bundle load error:', error);
                this.bundles = [];
                this.selectedBundleId = null;
                this.assignedProducts = [];
            } finally {
                this.isLoading = false;
            }
        },

        async loadAssignedProducts() {
            if (!this.selectedBundleId) {
                this.assignedProducts = [];

                return;
            }

            try {
                const criteria = new Criteria()
                    .addFilter(Criteria.equals('bundleId', this.selectedBundleId))
                    .addAssociation('product')
                    .addAssociation('product.options.group')
                    .addAssociation('bundle');

                const assignedProducts = await this.bundleAssignedProductsRepository.search(criteria, Shopware.Context.api);
                this.assignedProducts = [...assignedProducts];
            } catch (error) {
                console.error('Assigned products load error:', error);
                this.assignedProducts = [];
            }
        },

        async onSelectBundle(bundleId) {
            this.selectedBundleId = bundleId;
            this.selectedProduct = null;
            await this.loadAssignedProducts();
        },

        async onCreateBundle() {
            if (!this.product?.id) {
                console.error('Product not loaded');

                return;
            }

            try {
                const bundle = this.bundleRepository.create();
                bundle.productId = this.product.id;
                bundle.name = `${this.product.translated?.name || this.product.name} Bundle ${this.bundles.length + 1}`;
                bundle.description = '';
                bundle.active = true;

                await this.bundleRepository.save(bundle, Shopware.Context.api);
                await this.loadBundles(bundle.id);
            } catch (error) {
                console.error('Bundle create error:', error);
            }
        },

        async onAddProduct() {
            if (!this.selectedProduct?.id) {
                console.error('No product selected');
                return;
            }

            if (!this.product?.id) {
                console.error('Product not loaded');
                return;
            }

            try {
                const bundle = this.selectedBundle;

                if (!bundle?.id) {
                    console.error('No bundle selected');

                    return;
                }

                const existingAssignment = this.assignedProducts.find((item) => item.productId === this.selectedProduct.id);

                if (existingAssignment) {
                    existingAssignment.quantity += 1;
                    await this.bundleAssignedProductsRepository.save(existingAssignment, Shopware.Context.api);
                } else {
                    const assignment = this.bundleAssignedProductsRepository.create();
                    assignment.bundleId = bundle.id;
                    assignment.productId = this.selectedProduct.id;
                    assignment.quantity = 1;

                    await this.bundleAssignedProductsRepository.save(assignment, Shopware.Context.api);
                }

                await this.loadAssignedProducts();
                this.selectedProduct = null;
            } catch (error) {
                console.error('Save error:', error);
            }
        },

        async saveBundle() {
            const bundle = this.selectedBundle;

            if (!bundle) {
                return;
            }

            await this.bundleRepository.save(bundle, Shopware.Context.api);
            await this.loadBundles(bundle.id);
        },

        async onBundleFieldChange() {
            if (!this.selectedBundle) {
                return;
            }

            try {
                await this.saveBundle();
            } catch (error) {
                console.error('Bundle save error:', error);
            }
        },

        async deleteBundle() {
            const bundle = this.selectedBundle;

            if (!bundle?.id) {
                return;
            }

            await this.bundleRepository.delete(bundle.id, Shopware.Context.api);
            await this.loadBundles();
        },

        async deleteProduct(item) {
            await this.bundleAssignedProductsRepository.delete(item.id, Shopware.Context.api);
            await this.loadAssignedProducts();
        },

        async updateAssignedProduct(item) {
            await this.bundleAssignedProductsRepository.save(item, Shopware.Context.api);
            await this.loadAssignedProducts();
        },

        getBundleLabel(bundle) {
            return bundle?.name || `Bundle ${this.bundles.findIndex((item) => item.id === bundle?.id) + 1}`;
        },

        getProductLabel(item) {
            const product = item?.product;

            if (!product) {
                return item?.productId || '';
            }

            const baseName = product.translated?.name || product.name || product.productNumber || '';
            const options = product.options || [];

            if (!options.length) {
                return baseName;
            }

            const variantLabel = options
                .map((option) => option?.translated?.name || option?.name)
                .filter(Boolean)
                .join(' / ');

            return variantLabel ? `${baseName} - ${variantLabel}` : baseName;
        }
    }
});
