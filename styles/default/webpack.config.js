"use strict";

const path = require('path');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyPlugin = require("copy-webpack-plugin");

module.exports = {
    mode: 'production',
    entry: ['./src/js/frontend.js','./src/scss/default.scss'],
    output: {
        filename: 'theme.js',
        path: path.resolve(__dirname, 'dist'),
        clean: false
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'default.css'
        }),
        new CleanWebpackPlugin({
            protectWebpackAssets: true,
            cleanAfterEveryBuildPatterns: ['*.LICENSE.txt'],
        }),
        new CopyPlugin({
            patterns: [
                { from: "node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff", to: "fonts/" },
                { from: "node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff2", to: "fonts/" },
            ],
        }),
    ],
    module: {
        rules: [
            {
                test: /\.(scss)$/,
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