"use strict";

const { webpack, ProvidePlugin } = require("webpack");
const path = require('path');

const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyPlugin = require("copy-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");


module.exports = {
    mode: 'development',
    entry: ['./src/js/backend.js','./src/scss/backend.scss'],
    output: {
        filename: 'backend.js',
        path: path.resolve(__dirname, 'dist'),
        clean: false
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'backend.css'
        }),
        new CleanWebpackPlugin({
            protectWebpackAssets: true,
            cleanAfterEveryBuildPatterns: ['*.LICENSE.txt'],
        }),
        new ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            moment: 'moment'
        }),
        new CopyPlugin({
            patterns: [
                { from: "node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff", to: "fonts/" },
                { from: "node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff2", to: "fonts/" },
                { from: "node_modules/tinymce/", to: "tinymce/" },
                { from: "node_modules/@tinymce/tinymce-jquery/dist/tinymce-jquery.js", to: "tinymce-jquery/" },
                { from: "node_modules/ace-builds/src/mode-html.js", to: "ace/" },
                { from: "node_modules/ace-builds/src/theme-twilight.js", to: "ace/" },
                { from: "node_modules/ace-builds/src/theme-chrome.js", to: "ace/" },
                { from: "src/tinymce-languages", to: "tinymce-languages/" }
            ],
        }),
    ],
    optimization: {
        minimizer: [
            new TerserPlugin({
                parallel: true,
                terserOptions: {
                    keep_classnames: true,
                    keep_fnames: true
                },
            }),
        ]
    },
    module: {
        rules: [
            {
                test: /\.css$/i,
                use: ["style-loader", "css-loader"],
            },
            {
                test: /\.s[ac]ss$/i,
                exclude: /node_modules/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: {

                        }
                    },
                    {
                        loader: 'css-loader', options: { url: false, importLoaders: 1 }
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: [
                                    autoprefixer,
                                    cssnano
                                ]
                            }
                        }
                    },
                    {
                        loader: 'sass-loader'
                    }
                ]
            }
        ]
    }
}